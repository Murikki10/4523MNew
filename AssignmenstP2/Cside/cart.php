<?php
// 1. Start session to verify customer secure tokens
session_start();

// Security Guard: Redirect back to login portal if user session does not exist
if (!isset($_SESSION['cust_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Include the core parent database connection file
require_once('../conn.php');

if (!isset($conn)) {
    die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

$cid = $_SESSION['cust_id'];

// 🚀 Dynamic Data Fetching: Retrieve the customer's real registered address to pre-fill the input
$cust_stmt = $conn->prepare("SELECT caddr FROM Customers WHERE cid = ? LIMIT 1");
$cust_stmt->bind_param("i", $cid);
$cust_stmt->execute();
$cust_res = $cust_stmt->get_result();
$cust_row = $cust_res->fetch_assoc();
$default_address = isset($cust_row['caddr']) ? $cust_row['caddr'] : '';
$cust_stmt->close();

// ====================================================================================
// 🚀 BACKEND TRANS-LOCK PROCESSING: WHEN CHECKOUT FORM IS POSTED
// ====================================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_final_order'])) {
    $delivery_date = trim($_POST['odeliverydate']);
    $shipping_address = trim($_POST['odeliveraddress']);
    $total_amount = floatval($_POST['ototalamount']);
    $raw_cart_json = trim($_POST['cart_json_data']);
    $redirect_target = isset($_POST['redirect_target']) ? trim($_POST['redirect_target']) : 'dashboard.php';

    if (!empty($delivery_date) && !empty($shipping_address) && !empty($raw_cart_json)) {
        $cart_items = json_decode($raw_cart_json, true);

        if (!empty($cart_items) && is_array($cart_items)) {
            
            // 🔒 Start Advanced Database Transaction to enforce data integrity across tables
            mysqli_begin_transaction($conn);

            try {
                // 🚀 終極對齊修正：將預設狀態碼由 1 改為 2（即時進入 Processing 狀態）
                $order_sql = "INSERT INTO Orders (ototalamount, cid, odeliverydate, odeliveraddress, ostatus) VALUES (?, ?, ?, ?, 2)";
                $order_stmt = $conn->prepare($order_sql);
                $order_stmt->bind_param("diss", $total_amount, $cid, $delivery_date, $shipping_address);
                $order_stmt->execute();
                
                // Instantly capture the auto-generated unique Order ID (oid) for mapping
                $new_oid = $conn->insert_id;
                $order_stmt->close();

                // Step 2: Loop and insert individual pieces into OrderFurnitures mapping table
                $item_sql = "INSERT INTO OrderFurnitures (oid, fid, oqty) VALUES (?, ?, ?)";
                $item_stmt = $conn->prepare($item_sql);

                foreach ($cart_items as $item) {
                    $fid = intval($item['id']);
                    $oqty = intval($item['qty']);
                    
                    $item_stmt->bind_param("iii", $new_oid, $fid, $oqty);
                    $item_stmt->execute();
                }
                $item_stmt->close();

                // 🌟 Commit all statements together safely if no error occurs
                mysqli_commit($conn);

                // Dynamically route based on user choice inside the Success Modal
                if ($redirect_target === 'index.php') {
                    header("Location: index.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();

            } catch (Exception $e) {
                // Automatically rollback and wipe out corrupted entries if any server pipeline breaks
                mysqli_rollback($conn);
                die("Fatal Error: Checkout pipeline disrupted. Database changes rolled back safely. " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Premium Living</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="staff/staff_style.css">
    <link rel="stylesheet" href="cust_style.css">
    <style>
        /* 🌐 Extra UI Overlay Patch: Beautiful Frosted Glass Success Confirmation Dialog Box */
        .pl-success-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.7); display: none; align-items: center;
            justify-content: center; z-index: 99999; backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .pl-success-box {
            background: #ffffff; padding: 50px 40px; border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15); width: 90%; max-width: 500px;
            text-align: center; animation: plPopIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes plPopIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        
        .pl-success-icon {
            width: 80px; height: 80px; background: #e8f5e9; color: #27ae60;
            font-size: 40px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 25px; font-weight: bold;
        }
        .pl-success-box h2 { font-family: 'Playfair Display', serif; color: #2c3e50; font-size: 28px; margin: 0 0 12px; }
        .pl-success-box p { color: #7f8c8d; font-size: 14px; line-height: 1.6; margin: 0 0 35px; }
        
        .pl-success-actions { display: flex; flex-direction: column; gap: 12px; }
        .pl-btn-dash { background: #2c3e50; color: #ffffff; padding: 16px; border-radius: 12px; font-weight: 600; text-decoration: none; font-size: 15px; border: none; cursor: pointer; transition: all 0.2s; width: 100%; }
        .pl-btn-dash:hover { background: #3498db; transform: translateY(-2px); }
        .pl-btn-shop { background: #f8fafc; color: #475569; padding: 16px; border-radius: 12px; font-weight: 600; text-decoration: none; font-size: 15px; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; width: 100%; }
        .pl-btn-shop:hover { background: #f1f5f9; color: #2c3e50; }
    </style>
</head>
<body class="pl-cart-body">

<nav>
    <a href="index.php" class="logo">Premium<span>Living</span></a>
    <div class="nav-links">
        <a href="index.php">Collection</a>
        <a href="cart.php" style="color: #3498db; font-weight: bold;">Cart <span class="cart-count" id="cartCount">0</span></a>
        <a href="dashboard.php" style="margin-left: 20px; font-weight: bold; color: #2c3e50; text-decoration: none;">👤 Hi, <?php echo htmlspecialchars($_SESSION['cust_name']); ?></a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>Your Shopping Cart</h1>
    
    <div id="cartList"></div>

    <form action="cart.php" method="POST" id="pl-checkout-main-form">
        
        <input type="hidden" name="cart_json_data" id="pl-hidden-cart-data" value="">
        <input type="hidden" name="ototalamount" id="pl-hidden-total-amount" value="0.00">
        <input type="hidden" name="redirect_target" id="pl-redirect-target" value="dashboard.php">
        <input type="hidden" name="submit_final_order" value="1">

        <div class="checkout-section">
            <div class="form-card">
                <h3 style="margin-top:0; font-family:'Playfair Display', serif; font-size:20px; color:#2c3e50; margin-bottom:20px;">Delivery Details</h3>
                
                <label>Expected Delivery Date *</label>
                <input type="date" name="odeliverydate" id="finalDate" required>
                <div id="pl-delivery-notice" style="font-size: 12px; color: #e67e22; font-weight: 600; margin-top: -15px; margin-bottom: 20px;">
                    Loading earliest craftsmanship scheduling data...
                </div>
                
                <label>Shipping Address *</label>
                <input type="text" name="odeliveraddress" value="<?php echo htmlspecialchars($default_address); ?>" placeholder="Enter complete billing/shipping destination address" required>
                
                <label>Order Note (Optional)</label>
                <textarea id="finalNote" placeholder="Specify any customized dimensions or artisanal requests for this collection..." rows="3" style="resize:none;"></textarea>
            </div>

            <div class="summary-card">
                <h3 style="margin-top:0; font-family:'Playfair Display', serif; font-size:20px; color:#2c3e50; margin-bottom:20px;">Order Summary</h3>
                <div id="summaryDetails"></div>
                <button type="button" class="btn-pay" id="pl-fake-submit-trigger">Confirm Order & Pay</button>
            </div>
        </div>
    </form>
</div>

<div class="pl-success-overlay" id="successModal">
    <div class="pl-success-box">
        <div class="pl-success-icon">✓</div>
        <h2>Order Placed Successfully!</h2>
        <p>Your premium furniture collection request has been securely dispatched. All materials are now allocated to our workshop team for custom crafting.</p>
        
        <div class="pl-success-actions">
            <button type="button" class="pl-btn-dash" id="pl-modal-go-dash">View My Orders (Dashboard)</button>
            <button type="button" class="pl-btn-shop" id="pl-modal-go-shop">Continue Shopping</button>
        </div>
    </div>
</div>

<script src="cust_script.js"></script>
</body>
</html>