<?php
// 1. Start session to track customer authorization status
session_start();

// 2. Security Guard: Redirect to login portal if session does not exist
if (!isset($_SESSION['cust_id'])) {
    header("Location: login.php");
    exit();
}

// 3. Include the core database connection file
require_once('../conn.php');

if (!isset($conn)) {
    die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

$cid = $_SESSION['cust_id'];
$cname = $_SESSION['cust_name'];

// 🚀 Safe Query Fetching: Retrieve master order headers compatible with all MySQL environments
$order_query = "SELECT o.oid, o.odate, o.ototalamount, o.odeliverydate, o.odeliveraddress, o.ostatus
                FROM Orders o
                WHERE o.cid = ?
                ORDER BY o.odate DESC";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $cid);
$stmt->execute();
$orders_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Premium Living</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <!-- Include centralized global portal stylesheets -->
  <link rel="stylesheet" href="staff/staff_style.css">
  <link rel="stylesheet" href="cust_style.css">
  <style>
    /* Dynamic UI patch extensions for Cancelled states mapping */
    .pl-dash-body .status-cancelled { background: #ffebee !important; color: #c62828 !important; }
    .pl-dash-body .header-cancelled { background: #c62828 !important; }
    
    /* 🌐 Extra UI tuning for the new 4-step timeline configuration width distribution */
    .pl-dash-body .timeline .t-step { width: 25% !important; }
  </style>
</head>
<body class="pl-dash-body">

  <!-- Global Premium Navigation Bar -->
  <nav>
    <a href="index.php" class="logo">Premium<span>Living</span></a>
    <div class="nav-links">
      <a href="index.php">Collection</a>
      <a href="cart.php">Cart <span class="cart-count" id="cartCount">0</span></a>
      <a href="dashboard.php" style="color: #3498db; font-weight: bold;">👤 Hi, <?php echo htmlspecialchars($cname); ?></a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <div class="container">
    <div class="dashboard-header">
      <h1>Account Dashboard</h1>
      <p style="color: #7f8c8d;">Welcome back, <?php echo htmlspecialchars($cname); ?>. Manage your artisanal orders and profile.</p>
    </div>

    <!-- Analytics / Collector Profile Cards Overview -->
    <div class="function-grid">
      <div class="stat-card">
        <span class="icon">🏆</span>
        <h3>Member Status</h3>
        <p>Premium Collector</p>
      </div>

      <!-- 🌐 Replaced <div> with <a> to seamlessly link to profile_edit.php -->
      <a href="profile_edit.php" class="stat-card" style="display: block; text-decoration: none; color: inherit;">
        <span class="icon">👤</span>
        <h3>Client Profile</h3>
        <p><?php echo htmlspecialchars($cname); ?></p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
          <span style="font-size: 12px; color: #7f8c8d;">ID: #CUST-<?php echo $cid; ?></span>
          <span style="font-size: 11px; color: #3498db; font-weight: 600;">Edit Info →</span>
        </div>
      </a>
    </div>

    <!-- Filtering & Sorting Controllers Console -->
    <div class="section-header">
      <div class="section-title">Order History</div>
      <select class="sort-select" id="orderSort">
        <option value="date-desc">Newest First</option>
        <option value="date-asc">Oldest First</option>
        <option value="price-desc">Price: High to Low</option>
        <option value="price-asc">Price: Low to High</option>
      </select>
    </div>

    <!-- Active Orders Management Interactive Table Grid -->
    <div class="order-container">
      <table id="orderTable">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Order Date</th>
            <th>Expected Delivery</th>
            <th>Total Paid</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($orders_result && $orders_result->num_rows > 0) {
              while ($row = $orders_result->fetch_assoc()) {
                  $oid = $row['oid'];
                  $odate = date('Y-m-d', strtotime($row['odate']));
                  $odelivery = date('Y-m-d', strtotime($row['odeliverydate']));
                  $total = floatval($row['ototalamount']);
                  $status_code = intval($row['ostatus']);
                  $address = $row['odeliveraddress'];

                  // Secondary lookup loop to capture current ordered items specs
                  $items_array = array();
                  $items_query = "SELECT of.fid, of.oqty AS qty, f.fname AS name, f.fprice AS price 
                                  FROM OrderFurnitures `of`
                                  JOIN Furnitures f ON of.fid = f.fid
                                  WHERE of.oid = ?";
                  $item_stmt = $conn->prepare($items_query);
                  $item_stmt->bind_param("i", $oid);
                  $item_stmt->execute();
                  $items_res = $item_stmt->get_result();
                  while ($item_row = $items_res->fetch_assoc()) {
                      $items_array[] = $item_row;
                  }
                  $item_stmt->close();

                  // Encode items payload structures into standard raw JSON data attributes
                  $items_json = json_encode($items_array);

                  // 🚀 4-Step Alignment: Mapping integers perfectly to image_7bc0b6.png specifications
                  if ($status_code === 2) {
                      $status_text = 'Processing';
                      $status_class = 'status-pending';
                  } elseif ($status_code === 3) {
                      $status_text = 'Approved';
                      $status_class = 'status-progress';
                  } elseif ($status_code === 4) {
                      $status_text = 'Out for Delivery';
                      $status_class = 'status-progress';
                  } elseif ($status_code === 5) {
                      $status_text = 'Completed';
                      $status_class = 'status-delivered';
                  } else {
                      $status_text = 'Cancelled';
                      $status_class = 'status-cancelled';
                  }

                  echo '<tr data-date="' . $odate . '" data-price="' . $total . '">';
                  echo '  <td><strong>#ORD-' . $oid . '</strong></td>';
                  echo '  <td>' . $odate . '</td>';
                  echo '  <td>' . $odelivery . '</td>';
                  echo '  <td style="font-weight:600; color:#2c3e50;">$' . number_format($total, 2) . '</td>';
                  echo '  <td><span class="status-pill ' . $status_class . '">' . $status_text . '</span></td>';
                  echo '  <td>';
                  echo '    <button type="button" class="btn-manage" 
                                    data-oid="' . $oid . '" 
                                    data-date="' . $odate . '" 
                                    data-total="' . $total . '" 
                                    data-status="' . $status_text . '" 
                                    data-address="' . htmlspecialchars($address) . '" 
                                    data-items-json="' . htmlspecialchars($items_json) . '">';
                  echo '      View Receipt';
                  echo '    </button>';
                  echo '  </td>';
                  echo '  </tr>';
              }
          } else {
              echo '<tr><td colspan="6" style="text-align:center; color:#7f8c8d; padding:40px;">You haven\'t launched any custom boutique furniture collections yet.</td></tr>';
          }
          $stmt->close();
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 🌐 Premium Dynamic Order Invoice Breakdown Modal Popup -->
  <div class="modal-overlay" id="orderModal">
    <div class="modal-box">
      <!-- Modal Header Section -->
      <div class="modal-header-receipt" id="modalHeader">
        <button type="button" class="close-btn" id="pl-close-receipt-modal-btn">&times;</button>
        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 3px; opacity: 0.8;">Official Receipt</div>
        <h2 id="mID" style="margin: 10px 0; font-family: 'Playfair Display', serif; font-size: 28px;">#ORD-00000</h2>
        <div style="font-size: 14px; font-weight: 600;">Order Status: <span id="mStatusDisplay">Processing</span></div>

        <!-- 🌐 4-Step Production Pipeline Progress Timeline aligned with image_7bc0b6.png -->
        <div class="timeline" id="modalTimeline" style="margin-top: 25px;">
          <div class="t-step" id="step1">
            <div class="t-dot">1</div>
            <div class="t-label" style="font-size: 11px;">Processing</div>
          </div>
          <div class="t-step" id="step2">
            <div class="t-dot">2</div>
            <div class="t-label" style="font-size: 11px;">Approved</div>
          </div>
          <div class="t-step" id="step3">
            <div class="t-dot">3</div>
            <div class="t-label" style="font-size: 11px;">Out for Delivery</div>
          </div>
          <div class="t-step" id="step4">
            <div class="t-dot">4</div>
            <div class="t-label" style="font-size: 11px;">Completed</div>
          </div>
        </div>

        <!-- 🔒 Cancelled Notice Alert Guard Container -->
        <div id="pl-cancelled-notice" style="display:none; font-size: 18px; font-weight: 800; letter-spacing: 2px; margin-top: 15px; color: #fff;">
            ❌ CANCELLED
        </div>
      </div>

      <div class="modal-body">
        <!-- Logistics & Shipping Records Box -->
        <div class="receipt-section">
          <div class="receipt-section-title">Delivery Details</div>
          <div class="info-grid">
            <div class="info-item"><label>Recipient</label><span><?php echo htmlspecialchars($cname); ?></span></div>
            <div class="info-item"><label>Client ID</label><span>#CUST-<?php echo $cid; ?></span></div>
            <div class="info-item" style="grid-column: span 2;"><label>Shipping Address</label><span id="mReceiptAddress">Pending Update</span></div>
            <div class="info-item"><label>Delivery Est.</label><span id="mDate">0000-00-00</span></div>
          </div>
        </div>

        <!-- Product Formulation Cost Breakdown -->
        <div class="receipt-section">
          <div class="receipt-section-title">Items & Pricing</div>
          
          <!-- Dynamic lines insertion targeted destination element container -->
          <div id="pl-modal-receipt-items-list"></div>

          <div class="fee-details">
            <div class="fee-line"><span>Items Subtotal</span><span id="mReceiptSubtotal">$0.00</span></div>
            <div class="fee-line"><span>Premium Courier Shipping</span><span>$150.00</span></div>
            <div class="total-main">
              <span>TOTAL ORDER AMOUNT</span>
              <span id="mTotal">$0.00</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Dismissals Footer Control Panel -->
      <div class="modal-footer">
        <button type="button" id="pl-footer-close-receipt-btn" style="width:100%; background:#2c3e50; color:white; border:none; padding:14px; border-radius:12px; font-weight:600; cursor:pointer;">Close Receipt Window</button>
      </div>
    </div>
  </div>

  <!-- Inject Global Customer Script Engine Closure Logic -->
  <script src="cust_script.js"></script>
</body>
</html>