<?php
// Start session to track staff authorization status
session_start();

// Security Guard: Redirect to login if session does not exist
if (!isset($_SESSION['staff_id'])) {
  header("Location: staff_login.php");
  exit();
}

// Include the existing database connection file
require_once('../conn.php');

if (!isset($conn)) {
  die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

$success_message = '';
$error_message = '';

// ====================================================================================
// 🚀 核心修復 1：處理「列表下拉選單 (Dropdown)」及「彈窗狀態選單」的訂單狀態變更與扣減庫存
// ====================================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['oid']) && isset($_POST['ostatus']) && !isset($_POST['update_order_items'])) {
  $oid = intval($_POST['oid']);
  $new_status = intval($_POST['ostatus']);

  if ($oid > 0) {
    mysqli_begin_transaction($conn);
    try {
      // 1. Fetch current historical status to check transitions rules
      $status_stmt = $conn->prepare("SELECT ostatus FROM Orders WHERE oid = ?");
      $status_stmt->bind_param("i", $oid);
      $status_stmt->execute();
      $current_status = $status_stmt->get_result()->fetch_assoc()['ostatus'];
      $status_stmt->close();

      // 2. Inventory Deduction Logic: Trigger only if transitioning into Approved state (status = 3)
      if ($new_status == 3 && $current_status != 3 && $current_status != 4 && $current_status != 5) {
        $items_stmt = $conn->prepare("SELECT fid, oqty FROM OrderFurnitures WHERE oid = ?");
        $items_stmt->bind_param("i", $oid);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        $items_stmt->close();

        while ($item = $items_result->fetch_assoc()) {
          $fid = $item['fid'];
          $oqty = $item['oqty'];

          $recipe_stmt = $conn->prepare("SELECT mid, pmqty FROM FurnitureMaterials WHERE fid = ?");
          $recipe_stmt->bind_param("i", $fid);
          $recipe_stmt->execute();
          $recipe_result = $recipe_stmt->get_result();
          $recipe_stmt->close();

          while ($recipe = $recipe_result->fetch_assoc()) {
            $mid = $recipe['mid'];
            $required_total = $recipe['pmqty'] * $oqty;

            $stock_stmt = $conn->prepare("SELECT mname, mqty FROM Materials WHERE mid = ?");
            $stock_stmt->bind_param("i", $mid);
            $stock_stmt->execute();
            $stock_data = $stock_stmt->get_result()->fetch_assoc();
            $stock_stmt->close();

            if ($stock_data['mqty'] < $required_total) {
              throw new Exception("Inventory Shortage Error! Out of stock for material: " . $stock_data['mname']);
            }

            $deduct_stmt = $conn->prepare("UPDATE Materials SET mqty = mqty - ? WHERE mid = ?");
            $deduct_stmt->bind_param("ii", $required_total, $mid);
            $deduct_stmt->execute();
            $deduct_stmt->close();
          }
        }
      }

      // 3. Update the core order record state flag
      $update_stmt = $conn->prepare("UPDATE Orders SET ostatus = ? WHERE oid = ?");
      $update_stmt->bind_param("ii", $new_status, $oid);
      $update_stmt->execute();
      $update_stmt->close();

      mysqli_commit($conn);
      $success_message = "Order #" . $oid . " successfully updated to new transition stage status.";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Transaction Failure! " . $e->getMessage();
    }
  }
}

// ====================================================================================
// 🚀 核心修復 2：處理彈窗 Modal 內「Save Order Changes」儲存修改家具明細與即時重新計算總額
// ====================================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_items'])) {
  $oid = intval($_POST['oid']);
  $new_status = intval($_POST['ostatus']);
  
  $posted_fids = isset($_POST['fids']) ? $_POST['fids'] : [];
  $posted_oqtys = isset($_POST['oqtys']) ? $_POST['oqtys'] : [];

  if ($oid > 0) {
    mysqli_begin_transaction($conn);
    try {
      // 1. 先將該筆訂單原有的家具明細全部清除（覆蓋更新法）
      $clear_stmt = $conn->prepare("DELETE FROM OrderFurnitures WHERE oid = ?");
      $clear_stmt->bind_param("i", $oid);
      $clear_stmt->execute();
      $clear_stmt->close();

      $calculated_total = 0.00;

      // 2. 重新將留下來的家具項目與新數量寫入 OrderFurnitures，並從 DB 抓價格算總額
      if (!empty($posted_fids)) {
        for ($i = 0; $i < count($posted_fids); $i++) {
          $fid = intval($posted_fids[$i]);
          $qty = intval($posted_oqtys[$i]);

          if ($qty > 0) {
            // 從庫存型錄取得正確單價，防止前端價格被竄改
            $price_stmt = $conn->prepare("SELECT fprice FROM Furnitures WHERE fid = ?");
            $price_stmt->bind_param("i", $fid);
            $price_stmt->execute();
            $fprice = $price_stmt->get_result()->fetch_assoc()['fprice'];
            $price_stmt->close();

            $calculated_total += ($fprice * $qty);

            // 重新插入明細
            $ins_item = $conn->prepare("INSERT INTO OrderFurnitures (oid, fid, oqty) VALUES (?, ?, ?)");
            $ins_item->bind_param("iii", $oid, $fid, $qty);
            $ins_item->execute();
            $ins_item->close();
          }
        }
      }

      // 3. 更新訂單的主表資料：包括新狀態（ostatus）與重新計算後的總金額（ototalamount）
      $update_main = $conn->prepare("UPDATE Orders SET ostatus = ?, ototalamount = ? WHERE oid = ?");
      $update_main->bind_param("idi", $new_status, $calculated_total, $oid);
      $update_main->execute();
      $update_main->close();

      mysqli_commit($conn);
      $success_message = "Order #" . $oid . " configuration details and items catalog successfully updated.";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Failed to update order details: " . $e->getMessage();
    }
  }
}

// Fetch all orders logs records linked with dynamic inner joins to customer base logs records
$query = "SELECT o.oid, o.odate, o.ototalamount, o.ostatus, o.odeliveraddress, c.cname, c.ctel 
          FROM Orders o 
          JOIN Customers c ON o.cid = c.cid 
          ORDER BY o.oid DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders - Premium Living Staff</title>
  <link rel="stylesheet" href="staff_style.css">
</head>
<body>

  <div class="sidebar">
    <div class="logo">Premium Living<small>Staff Portal</small></div>
    <ul class="navbar">
      <li><a href="staff_index.php">📊 Dashboard</a></li>
      <li><a href="manage_orders.php" class="active">📦 Manage Orders</a></li>
      <li><a href="manage_furniture.php">🛋️ Manage Furniture</a></li>
      <li><a href="manage_materials.php">🪵 Manage Materials</a></li>
      <li><a href="sales_report.php">📈 Sales Report</a></li>
    </ul>
    <ul class="navbar bottom-nav">
      <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
  </div>

  <div class="content">
    <h2>Customer Orders Management</h2>

    <?php if ($success_message != '') echo "<div class='alert alert-success'>$success_message</div>"; ?>
    <?php if ($error_message != '') echo "<div class='alert alert-danger'>$error_message</div>"; ?>

    <div class="form-card" style="padding: 15px 20px; margin-bottom: 25px;">
      <div class="form-row" style="align-items: center; margin: 0; gap: 20px;">
        <div class="form-group name-group" style="margin: 0; flex: 2;">
          <label style="margin-bottom: 6px; font-size: 13px;">🔍 Search Customer Name</label>
          <input type="text" id="order-search-input" placeholder="Type customer name to filter orders instantly..." onkeyup="filterAndSortOrders()">
        </div>
        <div class="form-group price-group" style="margin: 0; flex: 1; max-width: 240px;">
          <label style="margin-bottom: 6px; font-size: 13px;">🔀 Sort Order By</label>
          <select id="order-sort-select" onchange="filterAndSortOrders()">
            <option value="date-desc">Date: Newest First</option>
            <option value="date-asc">Date: Oldest First</option>
            <option value="amount-desc">Amount: High to Low</option>
            <option value="amount-asc">Amount: Low to High</option>
          </select>
        </div>
      </div>
    </div>

    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Placement Date</th>
            <th>Customer Account</th>
            <th>Contact Phone</th>
            <th>Total Amount</th>
            <th>Pipeline Status</th>
            <th>Actions Control</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
              $items_q = "SELECT f.fid, f.fname, f.fprice, of.oqty 
                          FROM OrderFurnitures of 
                          JOIN Furnitures f ON of.fid = f.fid 
                          WHERE of.oid = " . intval($row['oid']);
              $items_res = mysqli_query($conn, $items_q);
              $items = [];
              while($i = mysqli_fetch_assoc($items_res)) {
                  $items[] = $i;
              }
            ?>
              <tr class="order-data-row" 
                  data-oid="<?php echo $row['oid']; ?>"
                  data-cname="<?php echo htmlspecialchars(strtolower($row['cname']), ENT_QUOTES, 'UTF-8'); ?>" 
                  data-ctel="<?php echo htmlspecialchars($row['ctel'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-caddr="<?php echo htmlspecialchars($row['odeliveraddress'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-ostatus="<?php echo $row['ostatus']; ?>"
                  data-items='<?php echo json_encode($items); ?>'
                  data-customer="<?php echo htmlspecialchars(strtolower($row['cname']), ENT_QUOTES, 'UTF-8'); ?>" 
                  data-timestamp="<?php echo strtotime($row['odate']); ?>" 
                  data-amount="<?php echo $row['ototalamount']; ?>"
                  onclick="handleRowClick(this)"
                  style="cursor: pointer;">
                <td><strong>#ORD-<?php echo str_pad($row['oid'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                <td><?php echo date('Y-m-d H:i', strtotime($row['odate'])); ?></td>
                <td><strong><?php echo htmlspecialchars($row['cname']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['ctel']); ?></td>
                <td style="font-weight: bold; color: #2c3e50;">$<?php echo number_format($row['ototalamount'], 2); ?></td>
                <td>
                  <span class="badge status-<?php echo $row['ostatus']; ?>">
                    <?php
                      if ($row['ostatus'] == 1) echo "🛒 Open/Cart";
                      elseif ($row['ostatus'] == 2) echo "⏳ Processing";
                      elseif ($row['ostatus'] == 3) echo "✅ Approved";
                      elseif ($row['ostatus'] == 4) echo "🚚 Out for Delivery";
                      elseif ($row['ostatus'] == 5) echo "📦 Completed";
                      else echo "❌ Cancelled/Rejected";
                    ?>
                  </span>
                </td>
                <td>
                  <form method="POST" action="manage_orders.php" style="display:inline-flex; gap:6px; align-items:center; margin:0;" onclick="event.stopPropagation();">
                    <input type="hidden" name="oid" value="<?php echo $row['oid']; ?>">
                    <div class="status-wrapper" style="padding: 4px 8px; font-size:13px;">
                      <select name="ostatus" onchange="this.form.submit()">
                        <option value="1" <?php if($row['ostatus'] == 1) echo 'selected'; ?>>🛒 Open/Cart</option>
                        <option value="2" <?php if($row['ostatus'] == 2) echo 'selected'; ?>>⏳ Processing</option>
                        <option value="3" <?php if($row['ostatus'] == 3) echo 'selected'; ?>>✅ Approved</option>
                        <option value="4" <?php if($row['ostatus'] == 4) echo 'selected'; ?>>🚚 Out for Delivery</option>
                        <option value="5" <?php if($row['ostatus'] == 5) echo 'selected'; ?>>📦 Completed</option>
                        <option value="0" <?php if($row['ostatus'] == 0) echo 'selected'; ?>>❌ Cancel/Reject</option>
                      </select>
                    </div>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align:center; color:#7f8c8d; padding:30px;">No operational customer orders records logged yet inside storage.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="orderDetailModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 650px; margin: 5% auto;">
      <form action="manage_orders.php" method="POST">
        <input type="hidden" id="modal-hidden-oid" name="oid">
        
        <div class="modal-header">
          <h3>Order Details <span id="modal-oid" style="color:#3498db;"></span></h3>
          <span class="close-btn" onclick="closeOrderModal()">&times;</span>
        </div>

        <div style="padding: 10px 5px;">
          <table style="width:100%; margin-bottom: 20px; font-size: 14px;">
            <tr>
              <td style="width:30%; padding:6px 0; color:#7f8c8d;">Customer Name:</td>
              <td style="padding:6px 0; font-weight:bold;" id="modal-cname"></td>
            </tr>
            <tr>
              <td style="padding:6px 0; color:#7f8c8d;">Contact Phone:</td>
              <td style="padding:6px 0;" id="modal-ctel"></td>
            </tr>
            <tr>
              <td style="padding:6px 0; color:#7f8c8d;">Delivery Address:</td>
              <td style="padding:6px 0;" id="modal-caddr"></td>
            </tr>
            <tr>
              <td style="padding:6px 0; color:#7f8c8d;">Order Status:</td>
              <td style="padding:6px 0;">
                <select name="ostatus" id="modal-status-select" style="padding:5px; border-radius:4px;">
                  <option value="1">🛒 Open/Cart</option>
                  <option value="2">⏳ Processing</option>
                  <option value="3">✅ Approved</option>
                  <option value="4">🚚 Out for Delivery</option>
                  <option value="5">📦 Completed</option>
                  <option value="0">❌ Cancel/Reject</option>
                </select>
              </td>
            </tr>
          </table>

          <div style="border-top: 1px solid #eee; padding-top: 15px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <h4 style="margin:0; color:#2c3e50;">Furniture Ordered Items</h4>
              <button type="button" id="edit-items-btn" class="btn" style="padding:5px 12px; font-size:12px; background:#34495e; color:white; border:none; border-radius:4px;" onclick="toggleEditMode()">Edit Items</button>
            </div>
            
            <div id="modal-items-container" style="max-height: 200px; overflow-y: auto; margin-bottom:15px;">
              </div>

            <div style="display:flex; justify-content: flex-end; font-size:16px; font-weight:bold; border-top:1px solid #eee; padding-top:10px;">
              <span>Grand Total: <span id="modal-total" style="color:#e74c3c;">$0.00</span></span>
            </div>
          </div>
        </div>

        <div style="margin-top: 25px; text-align: right; border-top: 1px solid #eee; padding-top:15px;">
          <button type="button" class="btn" style="background:#eee; color:#333; padding: 8px 20px; border:none; border-radius:4px;" onclick="closeOrderModal()">Cancel</button>
          <button type="submit" name="update_order_items" class="btn btn-primary" style="padding: 8px 25px; border:none; border-radius:4px; background:#3498db; color:white;">💾 Save Order Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script src="./manage_orders.js"></script>
</body>
</html>