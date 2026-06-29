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

// Handle Registration of New Material
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_material'])) {
  $mname = trim($_POST['mname']);
  $mqty = intval($_POST['mqty']);
  $munit = trim($_POST['munit']);

  if (!empty($mname) && $mqty >= 0) {
    // Use prepared statements to safely inject new raw material record
    $stmt = $conn->prepare("INSERT INTO Materials (mname, mqty, munit) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $mname, $mqty, $munit);

    if ($stmt->execute()) {
      $success_message = "New material '" . htmlspecialchars($mname) . "' registered successfully.";
    } else {
      $error_message = "Database processing error! Failed to register material.";
    }
    $stmt->close();
  } else {
    $error_message = "Invalid input data! Please verify material name and quantity.";
  }
}

// Handle Material Restocking (Incrementing stock levels)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restock_material'])) {
  $mid = intval($_POST['mid']);
  $restock_amount = intval($_POST['restock_amount']);

  if ($mid > 0 && $restock_amount > 0) {
    // Increment the material physical stock directly via database layer atomic addition
    $stmt = $conn->prepare("UPDATE Materials SET mqty = mqty + ? WHERE mid = ?");
    $stmt->bind_param("ii", $restock_amount, $mid);

    if ($stmt->execute()) {
      $success_message = "Stock successfully updated! Added " . $restock_amount . " units.";
    } else {
      $error_message = "Database processing error! Failed to restock material.";
    }
    $stmt->close();
  } else {
    $error_message = "Invalid restock amount. Must be greater than 0.";
  }
}

// Fetch all inventory items from Materials table to render in dashboard view
$query = "SELECT mid, mname, mqty, munit FROM Materials ORDER BY mid ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Material Control - Premium Living Staff</title>
  <style>
    /* --- Shared Layout (Consistent with your current progress) --- */
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

    /* Notification Feedback Box */
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

    /* --- Material Page Specific UI --- */
    .form-card,
    .table-card {
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
    }

    .form-row {
      display: flex;
      gap: 15px;
      align-items: flex-end;
    }

    .form-group {
      flex: 1;
    }

    .form-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 8px;
      font-size: 14px;
      color: #34495e;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #bdc3c7;
      border-radius: 4px;
    }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }

    .btn-primary {
      background: #3498db;
      color: white;
    }

    .btn-warning {
      background: #f39c12;
      color: white;
      font-size: 12px;
      padding: 6px 12px;
    }

    .btn-warning:hover {
      background: #e67e22;
    }

    /* Status Indicator */
    .low-stock {
      color: #e74c3c;
      font-weight: bold;
    }

    .normal-stock {
      color: #27ae60;
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    table th,
    table td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
      vertical-align: middle;
    }

    table th {
      background: #f8f9fa;
      color: #7f8c8d;
      font-size: 12px;
      text-transform: uppercase;
    }

    .restock-input {
      width: 70px;
      padding: 6px;
      border: 1px solid #bdc3c7;
      border-radius: 4px;
      text-align: center;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div class="logo">Premium Living<small>Staff Portal</small></div>
    <ul class="navbar">
      <li><a href="staff_index.php">📊 Dashboard</a></li>
      <li><a href="manage_orders.php">📦 Manage Orders</a></li>
      <li><a href="manage_furniture.php">🛋️ Manage Furniture</a></li>
      <li><a href="manage_materials.php" class="active">🪵 Manage Materials</a></li>
      <li><a href="sales_report.php">📈 Sales Report</a></li>
    </ul>
  </div>

  <div class="content">
    <h2>Material Inventory Control</h2>

    <?php if ($success_message != '')
      echo "<div class='alert alert-success'>$success_message</div>"; ?>
    <?php if ($error_message != '')
      echo "<div class='alert alert-danger'>$error_message</div>"; ?>

    <div class="form-card">
      <h4 style="margin-top:0;">Register New Material</h4>
      <form method="POST" action="manage_materials.php">
        <div class="form-row">
          <div class="form-group">
            <label>Material Name</label>
            <input type="text" name="mname" placeholder="e.g. High Density Foam" required>
          </div>
          <div class="form-group" style="flex: 0.5;">
            <label>Initial Quantity</label>
            <input type="number" name="mqty" value="0" min="0" required>
          </div>
          <div class="form-group" style="flex: 0.5;">
            <label>Unit</label>
            <select name="munit">
              <option value="pcs">pcs</option>
              <option value="meter">meter</option>
              <option value="block">block</option>
            </select>
          </div>
          <button type="submit" name="add_material" class="btn btn-primary">➕ Register</button>
        </div>
      </form>
    </div>

    <div class="table-card">
      <h4 style="margin-top:0;">Current Stock Levels</h4>
      <table>
        <thead>
          <tr>
            <th>MID</th>
            <th>Material Name</th>
            <th>Physical Quantity</th>
            <th>Unit</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              // Determine stock healthy status using threshold level logic (e.g., lower than 50 indicates low stock)
              $is_low = ($row['mqty'] < 50);
              $qty_class = $is_low ? 'low-stock' : '';
              $status_markup = $is_low ? '<span class="low-stock">⚠️ Low Stock</span>' : '<span class="normal-stock">● Healthy</span>';

              echo "<tr>";
              echo "<td>#M" . str_pad($row['mid'], 3, '0', STR_PAD_LEFT) . "</td>";
              echo "<td><strong>" . htmlspecialchars($row['mname']) . "</strong></td>";
              echo "<td class='" . $qty_class . "'>" . $row['mqty'] . "</td>";
              echo "<td>" . htmlspecialchars($row['munit']) . "</td>";
              echo "<td>" . $status_markup . "</td>";
              echo "<td>
                        <form method='POST' action='manage_materials.php' style='display:inline-flex; gap:8px; align-items:center; margin:0;'>
                            <input type='hidden' name='mid' value='" . $row['mid'] . "'>
                            <input type='number' name='restock_amount' class='restock-input' value='50' min='1' required>
                            <button type='submit' name='restock_material' class='btn btn-warning'>Restock</button>
                        </form>
                      </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align:center; color:#7f8c8d;'>No raw materials registered yet.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</body>

</html>