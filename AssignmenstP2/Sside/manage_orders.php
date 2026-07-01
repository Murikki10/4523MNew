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

// Handle Actions Control Dropdown Updates & Modal Status Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['oid']) && isset($_POST['ostatus']) && !isset($_POST['update_order_items'])) {
  $oid = intval($_POST['oid']);
  $new_status = intval($_POST['ostatus']);

  if ($oid > 0) {
    mysqli_begin_transaction($conn);
    try {
      $status_stmt = $conn->prepare("SELECT ostatus FROM Orders WHERE oid = ?");
      $status_stmt->bind_param("i", $oid);
      $status_stmt->execute();
      $current_status = $status_stmt->get_result()->fetch_assoc()['ostatus'];
      $status_stmt->close();

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

      $update_stmt = $conn->prepare("UPDATE Orders SET ostatus = ? WHERE oid = ?");
      $update_stmt->bind_param("ii", $new_status, $oid);
      $update_stmt->execute();
      $update_stmt->close();

      mysqli_commit($conn);
      $success_message = "Success: Order #" . $oid . " pipeline status updated successfully.";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Failure: " . $e->getMessage();
    }
  }
}

// Handle Modal Save Changes Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_items'])) {
  $oid = intval($_POST['oid']);
  $new_status = intval($_POST['ostatus']);
  $posted_fids = isset($_POST['fids']) ? $_POST['fids'] : [];
  $posted_oqtys = isset($_POST['oqtys']) ? $_POST['oqtys'] : [];

  if ($oid > 0) {
    mysqli_begin_transaction($conn);
    try {
      $clear_stmt = $conn->prepare("DELETE FROM OrderFurnitures WHERE oid = ?");
      $clear_stmt->bind_param("i", $oid);
      $clear_stmt->execute();
      $clear_stmt->close();

      $calculated_total = 0.00;
      if (!empty($posted_fids)) {
        for ($i = 0; $i < count($posted_fids); $i++) {
          $fid = intval($posted_fids[$i]);
          $qty = intval($posted_oqtys[$i]);

          if ($qty > 0) {
            $price_stmt = $conn->prepare("SELECT fprice FROM Furnitures WHERE fid = ?");
            $price_stmt->bind_param("i", $fid);
            $price_stmt->execute();
            $fprice = $price_stmt->get_result()->fetch_assoc()['fprice'];
            $price_stmt->close();

            $calculated_total += ($fprice * $qty);

            $ins_item = $conn->prepare("INSERT INTO OrderFurnitures (oid, fid, oqty) VALUES (?, ?, ?)");
            $ins_item->bind_param("iii", $oid, $fid, $qty);
            $ins_item->execute();
            $ins_item->close();
          }
        }
      }

      $update_main = $conn->prepare("UPDATE Orders SET ostatus = ?, ototalamount = ? WHERE oid = ?");
      $update_main->bind_param("idi", $new_status, $calculated_total, $oid);
      $update_main->execute();
      $update_main->close();

      mysqli_commit($conn);
      $success_message = "Success: Configuration for Order #" . $oid . " has been saved.";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Failed to update order details: " . $e->getMessage();
    }
  }
}

// Fetch Orders (排除 status = 1 嘅購物車單)
$query = "SELECT o.oid, o.odate, o.ototalamount, o.ostatus, o.odeliveraddress, o.odeliverydate, c.cname, c.ctel 
          FROM Orders o 
          JOIN Customers c ON o.cid = c.cid 
          WHERE o.ostatus != 1
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
  <style>
    /* FLOATING TOAST NOTIFICATIONS */
    .alert {
        position: fixed; bottom: 25px; right: 25px; z-index: 99999;
        min-width: 320px; max-width: 450px; padding: 16px 20px; border-radius: 8px;
        font-weight: bold; font-size: 14px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        display: flex; align-items: center; gap: 12px; opacity: 0; transform: translateX(50px);
        transition: opacity 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .alert.toast-show { opacity: 1; transform: translateX(0); }
    .alert.toast-hide { opacity: 0; transform: translateY(-20px); transition: opacity 0.4s ease, transform 0.4s ease; }
    .alert-success { background: #ffffff; color: #1e293b; border-left: 5px solid #2ecc71; border-top:none; border-right:none; border-bottom:none; }
    .alert-danger { background: #ffffff; color: #1e293b; border-left: 5px solid #e74c3c; border-top:none; border-right:none; border-bottom:none; }

    /* Timeline 樣式 */
    .timeline-container { display: flex; justify-content: space-between; position: relative; margin: 20px 0 30px 0; padding: 0 10px; }
    .timeline-container::before { content: ''; position: absolute; top: 15px; left: 40px; right: 40px; height: 3px; background: #e0e0e0; z-index: 1; }
    .timeline-step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; width: 20%; }
    .timeline-icon { width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 3px solid #e0e0e0; display: flex; align-items: center; justify-content: center; font-size: 14px; transition: all 0.3s; }
    .timeline-text { font-size: 12px; font-weight: bold; color: #95a5a6; margin-top: 6px; text-align: center; }
    .timeline-step.active .timeline-icon { border-color: #3498db; background: #3498db; color: white; box-shadow: 0 0 10px rgba(52,152,219,0.5); }
    .timeline-step.active .timeline-text { color: #3498db; }
    .timeline-step.completed .timeline-icon { border-color: #2ecc71; background: #2ecc71; color: white; }
    .timeline-step.completed .timeline-text { color: #2ecc71; }
    
    .material-box { background: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-top: 8px; font-size: 12px; }
    .material-item { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dotted #e2e8f0; }
    .material-item:last-child { border-bottom: none; }
    .stock-badge { padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .stock-ok { background: #e8f5e9; color: #2e7d32; }
    .stock-warn { background: #ffebee; color: #c62828; }
  </style>
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
      <div class="form-row" style="align-items: center; margin: 0; gap: 15px; flex-wrap: wrap;">
        <div class="form-group" style="margin: 0; flex: 2; min-width: 250px;">
          <label style="margin-bottom: 6px; font-size: 13px;">🔍 Omni Search Box</label>
          <input type="text" id="order-search-input" placeholder="Enter Order ID, Name, Tel, or Furniture Item..." onkeyup="filterAndSortOrders()">
        </div>
        <div class="form-group" style="margin: 0; flex: 1; min-width: 160px; max-width: 220px;">
          <label style="margin-bottom: 6px; font-size: 13px;">🎯 Filter by Status</label>
          <select id="order-status-filter" onchange="filterAndSortOrders()">
            <option value="all">✨ Show All Statuses</option>
            <option value="2">⏳ Processing</option>
            <option value="3">✅ Approved</option>
            <option value="4">🚚 Out for Delivery</option>
            <option value="5">📦 Completed</option>
            <option value="0">❌ Cancelled/Rejected</option>
          </select>
        </div>
        <div class="form-group" style="margin: 0; flex: 1; min-width: 160px; max-width: 220px;">
          <label style="margin-bottom: 6px; font-size: 13px;">🔀 Sort Order By</label>
          <select id="order-sort-select" onchange="filterAndSortOrders()">
            <option value="date-desc">Date: Newest First</option>
            <option value="date-asc">Date: Oldest First</option>
            <option value="amount-desc">Amount: High to Low</option>
            <option value="amount-asc">Amount: Low to High</option>
            <option value="status-asc">Status: Processing First</option>
            <option value="status-desc">Status: Completed First</option>
          </select>
        </div>
      </div>
    </div>

    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Order ID</th><th>Placement Date</th><th>Customer Account</th><th>Contact Phone</th><th>Total Amount</th><th>Pipeline Status</th><th>Actions Control</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
              $items_q = "SELECT of.oqty, f.fid, f.fname, f.fprice,
                                 GROUP_CONCAT(CONCAT(m.mname, ':', fm.pmqty, ':', m.mqty, ':', m.munit) SEPARATOR '||') AS materials_recipe
                          FROM OrderFurnitures of 
                          JOIN Furnitures f ON of.fid = f.fid 
                          LEFT JOIN FurnitureMaterials fm ON f.fid = fm.fid
                          LEFT JOIN Materials m ON fm.mid = m.mid
                          WHERE of.oid = " . intval($row['oid']) . "
                          GROUP BY f.fid";
              $items_res = mysqli_query($conn, $items_q);
              
              $items = [];
              $furniture_names_arr = [];
              while($i = mysqli_fetch_assoc($items_res)) {
                  $items[] = $i;
                  $furniture_names_arr[] = strtolower($i['fname']);
              }
              $furniture_names_string = implode(' | ', $furniture_names_arr);
            ?>
              <tr class="order-data-row" 
                  data-oid="<?php echo $row['oid']; ?>"
                  data-odate="<?php echo date('Y-m-d H:i', strtotime($row['odate'])); ?>"
                  data-cname="<?php echo htmlspecialchars(strtolower($row['cname']), ENT_QUOTES, 'UTF-8'); ?>" 
                  data-ctel="<?php echo htmlspecialchars($row['ctel'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-caddr="<?php echo htmlspecialchars($row['odeliveraddress'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-ddate="<?php echo date('Y-m-d', strtotime($row['odeliverydate'])); ?>"
                  data-ostatus="<?php echo $row['ostatus']; ?>"
                  data-items='<?php echo json_encode($items); ?>'
                  data-furnitures="<?php echo htmlspecialchars($furniture_names_string, ENT_QUOTES, 'UTF-8'); ?>"
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
                      if ($row['ostatus'] == 2) echo "⏳ Processing";
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
            <tr><td colspan="7" style="text-align:center; color:#7f8c8d; padding:30px;">No operational customer orders records logged yet inside storage.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="orderDetailModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 750px; margin: 3% auto;">
      <form action="manage_orders.php" method="POST">
        <input type="hidden" id="modal-hidden-oid" name="oid">
        <div class="modal-header">
          <h3>Order Full Analysis Panel <span id="modal-oid" style="color:#3498db;"></span></h3>
          <span class="close-btn" onclick="closeOrderModal()">&times;</span>
        </div>
        <div style="padding: 10px 5px;">
          <div class="timeline-container" id="modal-timeline">
            <div class="timeline-step" id="step-2"><div class="timeline-icon">⏳</div><div class="timeline-text">Processing</div></div>
            <div class="timeline-step" id="step-3"><div class="timeline-icon">✅</div><div class="timeline-text">Approved</div></div>
            <div class="timeline-step" id="step-4"><div class="timeline-icon">🚚</div><div class="timeline-text">On Delivery</div></div>
            <div class="timeline-step" id="step-5"><div class="timeline-icon">📦</div><div class="timeline-text">Completed</div></div>
            <div class="timeline-step" id="step-0"><div class="timeline-icon">❌</div><div class="timeline-text">Cancelled</div></div>
          </div>
          <table style="width:100%; margin-bottom: 20px; font-size: 14px; background: #fdfdfd; padding: 15px; border-radius: 8px; border: 1px solid #f0f0f0;">
            <tr>
              <td style="width:25%; padding:6px 0; color:#7f8c8d;">Customer Name:</td><td style="padding:6px 0; font-weight:bold;" id="modal-cname"></td>
              <td style="width:25%; padding:6px 0; color:#7f8c8d;">Order Date:</td><td style="padding:6px 0;" id="modal-odate"></td>
            </tr>
            <tr>
              <td style="padding:6px 0; color:#7f8c8d;">Contact Phone:</td><td style="padding:6px 0; font-weight:bold; color:#34495e;" id="modal-ctel"></td>
              <td style="padding:6px 0; color:#7f8c8d;">Est. Delivery:</td><td style="padding:6px 0; font-weight:bold; color:#e67e22;" id="modal-ddate"></td>
            </tr>
            <tr><td style="padding:6px 0; color:#7f8c8d;">Delivery Address:</td><td style="padding:6px 0;" id="modal-caddr" colspan="3"></td></tr>
            <tr>
              <td style="padding:6px 0; color:#7f8c8d;">Pipeline Management:</td>
              <td style="padding:6px 0;" colspan="3">
                <select name="ostatus" id="modal-status-select" style="padding:6px 12px; border-radius:4px; border:1px solid #cbd5e1; font-weight:bold;">
                  <option value="2">⏳ Processing (Pending Review)</option>
                  <option value="3">✅ Approved (Deduct Inventory)</option>
                  <option value="4">🚚 Out for Delivery</option>
                  <option value="5">📦 Completed (Finalized)</option>
                  <option value="0">❌ Cancel/Reject Order</option>
                </select>
              </td>
            </tr>
          </table>
          <div style="border-top: 1px solid #eee; padding-top: 15px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <h4 style="margin:0; color:#2c3e50; font-size:15px;">🛋️ Purchased Products Formula & Breakdown</h4>
              <button type="button" id="edit-items-btn" class="btn" style="padding:5px 12px; font-size:12px; background:#34495e; color:white; border:none; border-radius:4px;" onclick="toggleEditMode()">Edit Items</button>
            </div>
            <div id="modal-items-container" style="max-height: 280px; overflow-y: auto; margin-bottom:20px; padding-right:5px;"></div>
            <div style="background: #f1f5f9; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
              <h5 style="margin:0 0 10px 0; color:#475569; font-size:13px;">📊 Cumulative Material Demand Summary</h5>
              <div id="modal-total-materials-summary" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 12px;"></div>
            </div>
            <div style="border-top:1px solid #eee; padding-top:12px; font-size:14px; color:#475569;">
              <div style="display:flex; justify-content: flex-end; margin-bottom:4px;"><span style="width:150px; text-align:right;">Items Subtotal:</span><span id="modal-subtotal-price" style="width:100px; text-align:right; font-weight:bold;">$0.00</span></div>
              <div style="display:flex; justify-content: flex-end; margin-bottom:4px;"><span style="width:150px; text-align:right;">Shipping & Handling:</span><span style="width:100px; text-align:right; color:#2ecc71; font-weight:bold;">FREE</span></div>
              <div style="display:flex; justify-content: flex-end; font-size:18px; font-weight:bold; color:#b91c1c; margin-top:6px; border-top:1px dashed #eee; padding-top:6px;"><span style="width:150px; text-align:right;">Grand Total:</span><span id="modal-total" style="width:100px; text-align:right;">$0.00</span></div>
            </div>
          </div>
        </div>
        <div style="margin-top: 20px; text-align: right; border-top: 1px solid #eee; padding-top:15px;">
          <button type="button" class="btn" style="background:#eee; color:#333; padding: 8px 20px; border:none; border-radius:4px;" onclick="closeOrderModal()">Cancel</button>
          <button type="submit" name="update_order_items" class="btn btn-primary" style="padding: 8px 25px; border:none; border-radius:4px; background:#3498db; color:white;">💾 Save Order Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script src="./manage_orders.js"></script>
</body>
</html>