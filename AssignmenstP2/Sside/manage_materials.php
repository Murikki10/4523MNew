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

if (isset($_GET['delete_id'])) {
  $delete_mid = intval($_GET['delete_id']);

  if ($delete_mid > 0) {
    $check_stmt = $conn->prepare("SELECT COUNT(*) AS linked_furnitures FROM FurnitureMaterials WHERE mid = ?");
    $check_stmt->bind_param("i", $delete_mid);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($check_res['linked_furnitures'] > 0) {
      $error_message = "Deletion Denied! This material cannot be removed because it is currently linked inside existing furniture formula composition recipes.";
    } else {
      $del_stmt = $conn->prepare("DELETE FROM Materials WHERE mid = ?");
      $del_stmt->bind_param("i", $delete_mid);

      if ($del_stmt->execute()) {
        $success_message = "Material ID #" . $delete_mid . " has been permanently deleted from inventory storage.";
      } else {
        $error_message = "Failed to execute database deletion workflow.";
      }
      $del_stmt->close();
    }
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
  <link rel="stylesheet" href="staff_style.css">
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
    <ul class="navbar bottom-nav">
      <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
  </div>

  <div class="content">
    <h2>Material Inventory Control</h2>

    <?php if ($success_message != '')
      echo "<div class='alert alert-success'>$success_message</div>"; ?>
    <?php if ($error_message != '')
      echo "<div class='alert alert-danger'>$error_message</div>"; ?>

    <div class="form-card">
      <h4 style="margin-top:0; color:#34495e;">Register New Material</h4>
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
          <button type="submit" name="add_material" class="btn btn-primary"
            style="align-self: flex-end; margin-bottom: 4px; height: 40px; padding: 0 25px;">➕ Register</button>
        </div>
      </form>
    </div>

    <div class="table-card">
      <h4 style="margin-top:0; color:#34495e;">Current Stock Levels</h4>
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
                      <div style='display:inline-flex; align-items:center; gap:12px;'>
                        <form method='POST' action='manage_materials.php' style='display:inline-flex; gap:6px; align-items:center; margin:0;'>
                            <input type='hidden' name='mid' value='" . $row['mid'] . "'>
                            <input type='number' name='restock_amount' class='restock-input' value='50' min='1' style='width:65px; padding:5px; text-align:center; border:1px solid #ddd; border-radius:4px;' required>
                            <button type='submit' name='restock_material' class='btn btn-warning' style='padding:6px 12px; font-size:13px;'>Restock</button>
                        </form>
                        <a href='manage_materials.php?delete_id=" . $row['mid'] . "' class='btn btn-danger' style='padding:6px 14px; font-size:13px; text-decoration:none; text-align:center; border-radius:4px;' 
                           onclick=\"return confirm('Are you sure you want to permanently delete this material from inventory records?')\">🗑️ Delete</a>
                      </div>
                    </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align:center; color:#7f8c8d; padding:20px;'>No raw materials registered yet.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="./script.js"></script>
</body>

</html>