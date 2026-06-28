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

// Handle Insertion of New Furniture with Relational Material Composition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_furniture'])) {
  $fname = trim($_POST['fname']);
  $fprice = floatval($_POST['fprice']);
  $fdesc = trim($_POST['fdesc']);

  // Captured arrays from external script.js generated hidden fields
  $posted_mids = isset($_POST['mids']) ? $_POST['mids'] : [];
  $posted_pmqtys = isset($_POST['pmqtys']) ? $_POST['pmqtys'] : [];

  if (!empty($fname) && $fprice > 0) {
    // Begin Transaction to safeguard composite database multi-table writes
    mysqli_begin_transaction($conn);
    try {
      // 1. Insert core catalog data into main Furnitures table (fid is AUTO_INCREMENT)
      $ins_stmt = $conn->prepare("INSERT INTO Furnitures (fname, fdesc, fprice) VALUES (?, ?, ?)");
      $ins_stmt->bind_param("ssd", $fname, $fdesc, $fprice);
      $ins_stmt->execute();
      $new_fid = $conn->insert_id; // Retrieve generated Primary Key ID
      $ins_stmt->close();

      // 2. Loop through selected materials and insert composition records
      if (!empty($posted_mids)) {
        for ($i = 0; $i < count($posted_mids); $i++) {
          $mid = intval($posted_mids[$i]);
          $pmqty = intval($posted_pmqtys[$i]);

          if ($pmqty > 0) {
            $mat_stmt = $conn->prepare("INSERT INTO FurnitureMaterials (fid, mid, pmqty) VALUES (?, ?, ?)");
            $mat_stmt->bind_param("iii", $new_fid, $mid, $pmqty);
            $mat_stmt->execute();
            $mat_stmt->close();
          }
        }
      }

      // Commit all statements successfully
      mysqli_commit($conn);
      $success_message = "Furniture product '" . htmlspecialchars($fname) . "' registered successfully with ID #" . $new_fid;
    } catch (Exception $e) {
      // Rollback changes if any constraint or execution fails
      mysqli_rollback($conn);
      $error_message = "Database processing failure! Failed to register new furniture.";
    }
  } else {
    $error_message = "Invalid input! Please specify a valid product name and price.";
  }
}

// Handle Secure Furniture Record Deletion with Order Constraints Check
if (isset($_GET['delete_id'])) {
  $delete_fid = intval($_GET['delete_id']);

  // Relational Integrity Guard: Check if this specific furniture product is linked inside any active orders
  $check_stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM OrderFurnitures WHERE fid = ?");
  $check_stmt->bind_param("i", $delete_fid);
  $check_stmt->execute();
  $check_res = $check_stmt->get_result()->fetch_assoc();
  $check_stmt->close();

  if ($check_res['total_orders'] > 0) {
    // Business Rule Violation: Block deletion request to safeguard database history integrity
    $error_message = "Deletion Denied! This furniture product cannot be deleted because active customer order records exist for it.";
  } else {
    // Proceed with transactional cascade delete since it has zero active dependencies
    mysqli_begin_transaction($conn);
    try {
      // Remove relational configuration mappings first
      $del_relations = $conn->prepare("DELETE FROM FurnitureMaterials WHERE fid = ?");
      $del_relations->bind_param("i", $delete_fid);
      $del_relations->execute();
      $del_relations->close();

      // Remove from core catalog listing table
      $del_item = $conn->prepare("DELETE FROM Furnitures WHERE fid = ?");
      $del_item->bind_param("i", $delete_fid);
      $del_item->execute();
      $del_item->close();

      mysqli_commit($conn);
      $success_message = "Furniture product #" . $delete_fid . " has been removed from the catalog.";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Database processing error during deletion execution loop.";
    }
  }
}

// Fetch all available active stock materials raw logs to populate the configuration modal checklist
$materials_query = "SELECT mid, mname, munit FROM Materials ORDER BY mid ASC";
$materials_result = mysqli_query($conn, $materials_query);

// Fetch all catalog products dynamically with condensed raw materials specifications summary text
$furniture_query = "SELECT f.fid, f.fname, f.fdesc, f.fprice, 
                    IFNULL(GROUP_CONCAT(CONCAT(m.mname, ' x ', fm.pmqty) SEPARATOR ', '), 'No materials configured') AS materials_used
                    FROM Furnitures f
                    LEFT JOIN FurnitureMaterials fm ON f.fid = fm.fid
                    LEFT JOIN Materials m ON fm.mid = m.mid
                    GROUP BY f.fid
                    ORDER BY f.fid DESC";
$furniture_result = mysqli_query($conn, $furniture_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Furniture - Premium Living Staff</title>
  <style>
    /* --- Basic Layout (Sidebar Style) --- */
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

    /* Notification Alerts Feedback Styling */
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

    /* --- Page Specific UI --- */
    h2 {
      color: #2c3e50;
      border-bottom: 2px solid #3498db;
      padding-bottom: 10px;
      margin-top: 0;
    }

    .form-card {
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 40px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 8px;
      color: #34495e;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #bdc3c7;
      border-radius: 4px;
      font-size: 14px;
    }

    .form-row {
      display: flex;
      gap: 20px;
    }

    .form-row .form-group {
      flex: 1;
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

    .btn-primary:hover {
      background: #2980b9;
    }

    .btn-success {
      background: #2ecc71;
      color: white;
    }

    .btn-danger {
      background: #e74c3c;
      color: white;
      padding: 8px 16px;
      font-size: 13px;
      text-decoration: none;
      border-radius: 4px;
      display: inline-block;
    }

    .btn-danger:hover {
      background: #c0392b;
    }

    /* --- Modal Styles --- */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: white;
      margin: 8% auto;
      padding: 25px;
      border-radius: 8px;
      width: 60%;
      max-width: 700px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
      border-bottom: 1px solid #ddd;
      padding-bottom: 15px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .close-btn {
      cursor: pointer;
      font-size: 24px;
      color: #aaa;
    }

    .close-btn:hover {
      color: black;
    }

    /* --- Selected Materials Display --- */
    #selected-materials-display {
      border: 1px dashed #bdc3c7;
      padding: 15px;
      border-radius: 4px;
      min-height: 60px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      background: #fafafa;
    }

    .material-tag {
      background: #3498db;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .material-tag .remove-tag {
      cursor: pointer;
      font-weight: bold;
      color: #fff;
      border-left: 1px solid rgba(255, 255, 255, 0.3);
      padding-left: 8px;
    }

    /* --- Table Styles --- */
    .table-card {
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    table th,
    table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ecf0f1;
    }

    table th {
      background-color: #f8f9fa;
      color: #2c3e50;
    }

    .thumbnail {
      width: 50px;
      height: 50px;
      border-radius: 4px;
      object-fit: cover;
    }

    .price-tag {
      color: #27ae60;
      font-weight: bold;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div class="logo">Premium Living<small>Staff Portal</small></div>
    <ul class="navbar">
      <li><a href="staff_index.php">📊 Dashboard</a></li>
      <li><a href="manage_orders.php">📦 Manage Orders</a></li>
      <li><a href="manage_furniture.php" class="active">🛋️ Manage Furniture</a></li>
      <li><a href="manage_materials.php">🪵 Manage Materials</a></li>
      <li><a href="sales_report.php">📈 Sales Report</a></li>
    </ul>
    <ul class="navbar bottom-nav">
      <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
  </div>

  <div class="content">

    <?php if ($success_message != '')
      echo "<div class='alert alert-success'>$success_message</div>"; ?>
    <?php if ($error_message != '')
      echo "<div class='alert alert-danger'>$error_message</div>"; ?>

    <div class="form-card">
      <h2>Add New Furniture</h2>
      <form action="manage_furniture.php" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Furniture Name</label>
            <input type="text" name="fname" placeholder="e.g. Modern Sofa" required>
          </div>
          <div class="form-group">
            <label>Price ($)</label>
            <input type="number" name="fprice" min="0" step="0.01" placeholder="0.00" required>
          </div>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="fdesc" rows="3" placeholder="Enter product description..."></textarea>
        </div>

        <div class="form-group">
          <label>Material Composition</label>
          <div id="selected-materials-display">
            <span style="color: #95a5a6; font-size: 14px;">No materials selected. Click the button below to
              configure...</span>
          </div>
          <button type="button" class="btn btn-success" style="margin-top: 10px;" onclick="openModal()">🔍 Select &
            Configure Materials</button>
        </div>

        <button type="submit" name="add_furniture" class="btn btn-primary">➕ Add to Catalog</button>
      </form>
    </div>

    <div class="table-card">
      <h2>Product Catalog</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Materials Used</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($furniture_result && mysqli_num_rows($furniture_result) > 0): ?>
            <?php while ($f_row = mysqli_fetch_assoc($furniture_result)):
              // Since database structure does not store image paths, use systematic placeholder based on ID
              $image_id = ($f_row['fid'] <= 4) ? $f_row['fid'] : 1;
              ?>
              <tr>
                <td>#<?php echo str_pad($f_row['fid'], 3, '0', STR_PAD_LEFT); ?></td>
                <td><img src="../Sample Furniture Images/<?php echo $image_id; ?>.png" class="thumbnail"
                    alt="Furniture Image"></td>
                <td><strong><?php echo htmlspecialchars($f_row['fname']); ?></strong><br><small
                    style="color:#7f8c8d;"><?php echo htmlspecialchars($f_row['fdesc']); ?></small></td>
                <td class="price-tag">$<?php echo number_format($f_row['fprice'], 2); ?></td>
                <td><small><?php echo htmlspecialchars($f_row['materials_used']); ?></small></td>
                <td>
                  <a href="manage_furniture.php?delete_id=<?php echo $f_row['fid']; ?>" class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to delete this furniture product permanently from the catalog?')">🗑️
                    Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; color:#7f8c8d; padding:20px;">No furniture products found inside
                catalog.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="materialModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Configure Furniture Materials</h3>
        <span class="close-btn" onclick="closeModal()">&times;</span>
      </div>
      <div style="max-height: 400px; overflow-y: auto;">
        <table style="width: 100%;">
          <thead style="position: sticky; top: 0; background: white;">
            <tr>
              <th>Select</th>
              <th>Material Name</th>
              <th>Required Qty</th>
              <th>Unit</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($materials_result)): ?>
              <?php while ($m_row = mysqli_fetch_assoc($materials_result)): ?>
                <tr>
                  <td><input type="checkbox" class="mat-check" value="<?php echo $m_row['mid']; ?>"
                      data-name="<?php echo htmlspecialchars($m_row['mname'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                  <td><?php echo htmlspecialchars($m_row['mname']); ?></td>
                  <td><input type="number" class="mat-qty" placeholder="0" min="1" value="1" style="width: 70px;"></td>
                  <td><?php echo htmlspecialchars($m_row['munit']); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div style="margin-top: 25px; text-align: right;">
        <button type="button" class="btn" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="confirmSelection()">Confirm</button>
      </div>
    </div>
  </div>

  <script src="./script.js"></script>

</body>

</html>