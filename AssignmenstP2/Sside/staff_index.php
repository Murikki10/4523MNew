<?php
// Start session to track staff authentication status
session_start();

// Security Guard: If staff session does not exist, redirect to login page immediately
if (!isset($_SESSION['staff_id']) || !isset($_SESSION['staff_name'])) {
    header("Location: staff_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Home - Premium Living Furniture</title>
    <link rel="stylesheet" href="staff_style.css">
</head>
<body>

    <div class="top-bar">
        <div class="logo-section">🛋️ Premium Living Staff</div>
        <div class="user-info">
            <span class="user-name">👤 Welcome, <strong><?php echo htmlspecialchars($_SESSION['staff_name']); ?></strong></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="main-container">
        <div class="welcome-msg">
            <h1>Centralized Management System</h1>
            <p>Please select a management function below.</p>
        </div>

        <div class="function-grid">
            <a href="manage_orders.php" class="func-card">
                <div class="icon">📦</div>
                <h3>Manage Orders</h3>
                <p>Approve/Reject orders and update delivery status.</p>
            </a>

            <a href="manage_furniture.php" class="func-card">
                <div class="icon">🛋️</div>
                <h3>Furniture Inventory</h3>
                <p>Add, update or delete furniture products.</p>
            </a>

            <a href="manage_materials.php" class="func-card">
                <div class="icon">🪵</div>
                <h3>Material Control</h3>
                <p>Monitor and restock material quantities.</p>
            </a>

            <a href="sales_report.php" class="func-card">
                <div class="icon">📊</div>
                <h3>Sales Report</h3>
                <p>View total sales amount and product statistics.</p>
            </a>
        </div>
    </div>

</body>
</html>