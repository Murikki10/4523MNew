<?php
// Force display errors for deep debugging phase
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Handle Order Modification and Status/Inventory Management Lifecycle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_order_changes'])) {
    $oid = intval($_POST['oid']);
    $new_status = intval($_POST['ostatus']);

    $posted_fids = isset($_POST['fids']) ? $_POST['fids'] : [];
    $posted_oqtys = isset($_POST['oqtys']) ? $_POST['oqtys'] : [];

    // Begin Transaction to maintain absolute data integrity
    mysqli_begin_transaction($conn);

    try {
        // 1. Fetch current status and old item quantities to revert stock if previously approved
        $old_status_stmt = $conn->prepare("SELECT ostatus FROM Orders WHERE oid = ?");
        $old_status_stmt->bind_param("i", $oid);
        $old_status_stmt->execute();
        $old_status_res = $old_status_stmt->get_result();

        $old_status = 1;
        if ($old_status_res->num_rows > 0) {
            $old_status = $old_status_res->fetch_assoc()['ostatus'];
        }
        $old_status_stmt->close();

        // If previous state was Approved (3), return the old quantities of materials back to inventory
        if ($old_status == 3) {
            $old_items_stmt = $conn->prepare("SELECT fid, oqty FROM OrderFurnitures WHERE oid = ?");
            $old_items_stmt->bind_param("i", $oid);
            $old_items_stmt->execute();
            $old_items_res = $old_items_stmt->get_result();

            while ($item = $old_items_res->fetch_assoc()) {
                $old_fid = $item['fid'];
                $old_qty = $item['oqty'];

                $mat_stmt = $conn->prepare("SELECT mid, pmqty FROM FurnitureMaterials WHERE fid = ?");
                $mat_stmt->bind_param("i", $old_fid);
                $mat_stmt->execute();
                $mat_res = $mat_stmt->get_result();

                while ($mat = $mat_res->fetch_assoc()) {
                    $return_qty = $mat['pmqty'] * $old_qty;
                    $return_stmt = $conn->prepare("UPDATE Materials SET mqty = mqty + ? WHERE mid = ?");
                    $return_stmt->bind_param("ii", $return_qty, $mat['mid']);
                    $return_stmt->execute();
                    $return_stmt->close();
                }
                $mat_stmt->close();
            }
            $old_items_stmt->close();
        }

        // 2. Clear out old items inside OrderFurnitures for this order to apply fresh edits safely
        $delete_stmt = $conn->prepare("DELETE FROM OrderFurnitures WHERE oid = ?");
        $delete_stmt->bind_param("i", $oid);
        $delete_stmt->execute();
        $delete_stmt->close();

        if (empty($posted_fids)) {
            $new_status = 0;
        }

        $new_total_amount = 0.00;
        $materials_needed = [];

        // 3. Insert active/modified items back and aggregate material needs
        for ($i = 0; $i < count($posted_fids); $i++) {
            $fid = intval($posted_fids[$i]);
            $oqty = intval($posted_oqtys[$i]);
            if ($oqty <= 0)
                continue;

            $price_stmt = $conn->prepare("SELECT fprice FROM Furnitures WHERE fid = ?");
            $price_stmt->bind_param("i", $fid);
            $price_stmt->execute();
            $price_res = $price_stmt->get_result();

            $fprice = 0.00;
            if ($price_res->num_rows > 0) {
                $fprice = $price_res->fetch_assoc()['fprice'];
            }
            $price_stmt->close();

            $new_total_amount += ($fprice * $oqty);

            $ins_item_stmt = $conn->prepare("INSERT INTO OrderFurnitures (oid, fid, oqty) VALUES (?, ?, ?)");
            $ins_item_stmt->bind_param("iii", $oid, $fid, $oqty);
            $ins_item_stmt->execute();
            $ins_item_stmt->close();

            $mat_stmt = $conn->prepare("SELECT mid, pmqty FROM FurnitureMaterials WHERE fid = ?");
            $mat_stmt->bind_param("i", $fid);
            $mat_stmt->execute();
            $mat_res = $mat_stmt->get_result();
            while ($mat = $mat_res->fetch_assoc()) {
                $mid = $mat['mid'];
                $required_units = $mat['pmqty'] * $oqty;
                if (!isset($materials_needed[$mid])) {
                    $materials_needed[$mid] = 0;
                }
                $materials_needed[$mid] += $required_units;
            }
            $mat_stmt->close();
        }

        // 4. If the new intended status is Approved (3), execute stock limit checks
        if ($new_status == 3 && !empty($materials_needed)) {
            foreach ($materials_needed as $mid => $total_req) {
                $check_stock_stmt = $conn->prepare("SELECT mname, mqty FROM Materials WHERE mid = ?");
                $check_stock_stmt->bind_param("i", $mid);
                $check_stock_stmt->execute();
                $stock_res = $check_stock_stmt->get_result();

                if ($stock_res->num_rows > 0) {
                    $stock_data = $stock_res->fetch_assoc();
                    if ($stock_data['mqty'] < $total_req) {
                        throw new Exception("Insufficient stock for raw material: " . $stock_data['mname'] . " (Required: " . $total_req . ", Available: " . $stock_data['mqty'] . ")");
                    }

                    $deduct_stmt = $conn->prepare("UPDATE Materials SET mqty = mqty - ? WHERE mid = ?");
                    $deduct_stmt->bind_param("ii", $total_req, $mid);
                    $deduct_stmt->execute();
                    $deduct_stmt->close();
                }
                $check_stock_stmt->close();
            }
        }

        // 5. Commit updates back into main Orders table
        $update_order_stmt = $conn->prepare("UPDATE Orders SET ostatus = ?, ototalamount = ? WHERE oid = ?");
        $update_order_stmt->bind_param("idi", $new_status, $new_total_amount, $oid);
        $update_order_stmt->execute();
        $update_order_stmt->close();

        mysqli_commit($conn);
        $success_message = "Order #" . $oid . " updates and inventory changes committed successfully.";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = $e->getMessage();
    }
}

// Fetch records with secure LEFT JOIN syntax mapping layout
$orders_query = "SELECT o.oid, o.odate, o.ototalamount, o.ostatus, o.odeliveraddress, 
                        IFNULL(c.cname, 'Guest Customer') AS cname, 
                        IFNULL(c.ctel, 'N/A') AS ctel 
                 FROM Orders o 
                 LEFT JOIN Customers c ON o.cid = c.cid 
                 ORDER BY o.odate DESC";
$orders_result = mysqli_query($conn, $orders_query);

$all_orders = [];
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $oid = $row['oid'];
        $row['items'] = [];

        $items_query = "SELECT of.fid, of.oqty, f.fname, f.fprice 
                        FROM OrderFurnitures of 
                        LEFT JOIN Furnitures f ON of.fid = f.fid 
                        WHERE of.oid = " . $oid;
        $items_result = mysqli_query($conn, $items_query);
        if ($items_result) {
            while ($item = mysqli_fetch_assoc($items_result)) {
                if (empty($item['fname'])) {
                    $item['fname'] = "Unknown Product (ID: " . $item['fid'] . ")";
                }
                $row['items'][] = $item;
            }
        }
        $all_orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Premium Living Staff</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            background-color: #f4f7f6;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
        }

        .sidebar .logo {
            font-size: 22px;
            text-align: center;
            font-weight: bold;
            padding: 30px 20px;
            background: #1a252f;
            border-bottom: 1px solid #34495e;
        }

        .sidebar .logo small {
            display: block;
            font-size: 14px;
            color: #1abc9c;
            margin-top: 5px;
        }

        .sidebar ul.navbar {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul.navbar li a {
            display: block;
            padding: 18px 25px;
            color: #ecf0f1;
            text-decoration: none;
            border-bottom: 1px solid #34495e;
            transition: 0.3s;
        }

        .sidebar ul.navbar li a:hover,
        .sidebar ul.navbar li a.active {
            background: #3498db;
            padding-left: 30px;
        }

        .sidebar ul.navbar.bottom-nav {
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 0;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8f9fa;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
            cursor: pointer;
        }

        table tr:hover {
            background: #f1f9ff;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-1 {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-2 {
            background: #fef3c7;
            color: #d97706;
        }

        .status-3 {
            background: #dcfce7;
            color: #15803d;
        }

        .status-0 {
            background: #fee2e2;
            color: #b91c1c;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }

        .order-card {
            background: white;
            width: 600px;
            margin: 5% auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 30px;
        }

        .status-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .status-wrapper select {
            border: none;
            background: transparent;
            font-weight: bold;
            color: #2c3e50;
            cursor: pointer;
            outline: none;
        }

        .detail-section {
            margin-bottom: 25px;
        }

        .detail-section h4 {
            color: #3498db;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
            padding-left: 10px;
            margin-top: 0;
        }

        .items-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .item-row {
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .item-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            display: block;
            font-weight: bold;
            color: #2c3e50;
        }

        .item-price-detail {
            font-size: 13px;
            color: #7f8c8d;
        }

        .item-subtotal {
            font-weight: bold;
            color: #2ecc71;
            width: 90px;
            text-align: right;
        }

        .item-edit-controls {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #eee;
            display: none;
            justify-content: flex-end;
            gap: 10px;
            align-items: center;
        }

        .edit-qty {
            width: 70px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .btn-remove {
            background: #ff4757;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-small-edit {
            font-size: 12px;
            padding: 6px 15px;
            background: #34495e;
            color: white;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .card-footer {
            background: #f1f4f6;
            padding: 20px;
            text-align: right;
        }

        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }
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
        <h2>Manage Customer Orders</h2>

        <?php if ($success_message != '')
            echo "<div class='alert alert-success'>$success_message</div>"; ?>
        <?php if ($error_message != '')
            echo "<div class='alert alert-danger'>$error_message</div>"; ?>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Customer Name</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_orders)): ?>
                        <?php foreach ($all_orders as $order):
                            $status_label = "Unknown";
                            if ($order['ostatus'] == 1)
                                $status_label = "Open";
                            elseif ($order['ostatus'] == 2)
                                $status_label = "Processing";
                            elseif ($order['ostatus'] == 3)
                                $status_label = "Approved";
                            elseif ($order['ostatus'] == 0)
                                $status_label = "Rejected";
                            ?>
                            <tr class="order-row" data-oid="<?php echo $order['oid']; ?>"
                                data-cname="<?php echo htmlspecialchars($order['cname'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-ctel="<?php echo htmlspecialchars($order['ctel'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-caddr="<?php echo htmlspecialchars($order['odeliveraddress'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-ostatus="<?php echo $order['ostatus']; ?>"
                                data-items="<?php echo htmlspecialchars(json_encode($order['items']), ENT_QUOTES, 'UTF-8'); ?>"
                                onclick="handleRowClick(this)">
                                <td><strong>#<?php echo $order['oid']; ?></strong></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($order['odate'])); ?></td>
                                <td><?php echo htmlspecialchars($order['cname']); ?></td>
                                <td style="color:#27ae60; font-weight:bold;">
                                    $<?php echo number_format($order['ototalamount'], 2); ?></td>
                                <td><span
                                        class="badge status-<?php echo $order['ostatus']; ?>"><?php echo $status_label; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#7f8c8d; padding:20px;">No orders found in the
                                database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="orderDetailModal" class="modal">
        <form method="POST" action="manage_orders.php" class="order-card">
            <input type="hidden" id="modal-hidden-oid" name="oid">

            <div class="card-header">
                <span>Order Details <strong id="modal-oid"></strong></span>
                <span style="cursor:pointer; font-size:24px;" onclick="closeOrderModal()">&times;</span>
            </div>

            <div class="card-body">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:25px;">
                    <div>
                        <h4 style="margin:0; color:#7f8c8d; font-size:12px; text-transform:uppercase;">Customer</h4>
                        <strong id="modal-cname" style="font-size:18px;"></strong><br>
                        <span id="modal-ctel" style="color:#7f8c8d; font-size:14px;"></span>
                    </div>
                    <div class="status-wrapper">
                        <span style="font-size:12px; color:#95a5a6;">STATUS:</span>
                        <select id="modal-status-select" name="ostatus">
                            <option value="1">🔵 OPEN</option>
                            <option value="2">🟡 PROCESSING</option>
                            <option value="3">🟢 APPROVED</option>
                            <option value="0">🔴 REJECTED</option>
                        </select>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="items-list-header">
                        <h4>Order Items</h4>
                        <button type="button" class="btn-small-edit" id="edit-items-btn" onclick="toggleEditMode()">Edit
                            Items</button>
                    </div>
                    <div id="modal-items-container"></div>
                </div>

                <div class="detail-section"
                    style="background:#f8f9fa; padding:15px; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:bold; color:#7f8c8d;">TOTAL AMOUNT</span>
                    <span id="modal-total" style="font-size:24px; font-weight:bold; color:#27ae60;"></span>
                </div>

                <div class="detail-section">
                    <h4>Shipping Address</h4>
                    <p id="modal-caddr"
                        style="font-size:14px; color:#2c3e50; background:#fff; border:1px solid #eee; padding:10px; border-radius:4px; margin:0;">
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <button type="button" class="btn" style="background:#eee;" onclick="closeOrderModal()">Cancel</button>
                <button type="submit" name="save_order_changes" class="btn btn-primary">Save All Changes</button>
            </div>
        </form>
    </div>

    <script>
        let currentOrderItems = [];
        let editModeActive = false;

        function handleRowClick(rowElement) {
            const orderData = {
                oid: rowElement.getAttribute('data-oid'),
                cname: rowElement.getAttribute('data-cname'),
                ctel: rowElement.getAttribute('data-ctel'),
                odeliveraddress: rowElement.getAttribute('data-caddr'),
                ostatus: rowElement.getAttribute('data-ostatus'),
                items: JSON.parse(rowElement.getAttribute('data-items'))
            };
            openOrderModal(orderData);
        }

        function openOrderModal(order) {
            document.getElementById('modal-hidden-oid').value = order.oid;
            document.getElementById('modal-oid').innerText = '#' + order.oid;
            document.getElementById('modal-cname').innerText = order.cname;
            document.getElementById('modal-ctel').innerText = order.ctel;
            document.getElementById('modal-caddr').innerText = order.odeliveraddress;
            document.getElementById('modal-status-select').value = order.ostatus;

            currentOrderItems = Array.isArray(order.items) ? JSON.parse(JSON.stringify(order.items)) : [];
            editModeActive = false;

            const btn = document.getElementById("edit-items-btn");
            if (btn) {
                btn.innerText = "Edit Items";
                btn.style.background = "#34495e";
            }

            renderModalItems();
            document.getElementById('orderDetailModal').style.display = 'block';
        }

        function closeOrderModal() {
            document.getElementById('orderDetailModal').style.display = 'none';
        }

        function renderModalItems() {
            const container = document.getElementById('modal-items-container');
            container.innerHTML = '';

            if (currentOrderItems.length === 0) {
                container.innerHTML = '<p style="color:#7f8c8d; font-size:14px; text-align:center; padding:10px;">No items inside this order.</p>';
                calculateModalTotal();
                return;
            }

            currentOrderItems.forEach((item, index) => {
                const subtotal = item.fprice * item.oqty;
                const displayStyle = editModeActive ? 'flex' : 'none';

                const rowHTML = `
            <div class="item-row" id="item-row-${index}">
                <input type="hidden" name="fids[]" value="${item.fid}">
                <div class="item-main">
                    <div class="item-info">
                        <span class="item-name">${item.fname}</span>
                        <span class="item-price-detail">$${parseFloat(item.fprice).toFixed(2)} each</span>
                    </div>
                    <div class="item-subtotal" id="subtotal-text-${index}">$${subtotal.toFixed(2)}</div>
                </div>
                <div class="item-edit-controls" id="edit-controls-${index}" style="display: ${displayStyle}">
                    <label style="font-size:13px; color:#555;">Qty: </label>
                    <input type="number" class="edit-qty" name="oqtys[]" value="${item.oqty}" min="1" oninput="updateItemQty(${index}, this.value)">
                    <button type="button" class="btn-remove" onclick="removeItem(${index})">Remove</button>
                </div>
            </div>
        `;
                container.insertAdjacentHTML('beforeend', rowHTML);
            });

            calculateModalTotal();
        }

        function toggleEditMode() {
            const btn = document.getElementById("edit-items-btn");
            editModeActive = !editModeActive;

            currentOrderItems.forEach((item, index) => {
                const controls = document.getElementById(`edit-controls-${index}`);
                if (controls) {
                    controls.style.display = editModeActive ? "flex" : "none";
                }
            });

            btn.innerText = editModeActive ? "Finish Editing" : "Edit Items";
            btn.style.background = editModeActive ? "#27ae60" : "#34495e";
        }

        function updateItemQty(index, val) {
            const quantity = parseInt(val);
            if (isNaN(quantity) || quantity <= 0) return;

            currentOrderItems[index].oqty = quantity;
            const subtotal = currentOrderItems[index].fprice * quantity;
            const subtotalText = document.getElementById(`subtotal-text-${index}`);
            if (subtotalText) subtotalText.innerText = '$' + subtotal.toFixed(2);

            calculateModalTotal();
        }

        function removeItem(index) {
            if (confirm("Are you sure you want to remove this furniture product from this order?")) {
                currentOrderItems.splice(index, 1);
                renderModalItems();
                if (editModeActive) {
                    editModeActive = false;
                    toggleEditMode();
                }
            }
        }

        function calculateModalTotal() {
            let grandTotal = 0;
            currentOrderItems.forEach(item => {
                grandTotal += item.fprice * item.oqty;
            });
            const totalEl = document.getElementById('modal-total');
            if (totalEl) totalEl.innerText = '$' + grandTotal.toFixed(2);
        }

        window.onclick = function (event) {
            const modal = document.getElementById('orderDetailModal');
            if (event.target == modal) {
                closeOrderModal();
            }
        }
    </script>
</body>

</html>