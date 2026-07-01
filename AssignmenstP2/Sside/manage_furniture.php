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

// Handle Insertion of New Furniture with Relational Material Composition and Image Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_furniture'])) {
  $fname = trim($_POST['fname']);
  $fprice = floatval($_POST['fprice']);
  $fdesc = trim($_POST['fdesc']);

  $posted_mids = isset($_POST['mids']) ? $_POST['mids'] : [];
  $posted_pmqtys = isset($_POST['pmqtys']) ? $_POST['pmqtys'] : [];

  if (!empty($fname) && $fprice > 0) {
    mysqli_begin_transaction($conn);
    try {
      $ins_stmt = $conn->prepare("INSERT INTO Furnitures (fname, fdesc, fprice) VALUES (?, ?, ?)");
      $ins_stmt->bind_param("ssd", $fname, $fdesc, $fprice);
      $ins_stmt->execute();
      $new_fid = $conn->insert_id;
      $ins_stmt->close();

      if (isset($_FILES['fimage']) && $_FILES['fimage']['error'] == 0) {
        $target_dir = "../Sample Furniture Images/";
        $target_file = $target_dir . $new_fid . ".png";

        if (!file_exists($target_dir)) {
          mkdir($target_dir, 0777, true);
        }

        if (!move_uploaded_file($_FILES['fimage']['tmp_name'], $target_file)) {
          throw new Exception("Image storage failed! Could not move uploaded file.");
        }
      } else {
        throw new Exception("Please select a valid image file to upload.");
      }

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

      mysqli_commit($conn);
      $success_message = "Furniture product '" . htmlspecialchars($fname) . "' registered successfully with ID #" . $new_fid;
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Processing failure: " . $e->getMessage();
    }
  } else {
    $error_message = "Invalid input! Please specify a valid product name and price.";
  }
}

// 🌐 CORE INTEGRATION: Handle Modification/Update of Existing Furniture Records
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_furniture'])) {
  $fid = intval($_POST['fid']);
  $fname = trim($_POST['fname']);
  $fprice = floatval($_POST['fprice']);
  $fdesc = trim($_POST['fdesc']);

  $posted_mids = isset($_POST['edit_mids']) ? $_POST['edit_mids'] : [];
  $posted_pmqtys = isset($_POST['edit_pmqtys']) ? $_POST['edit_pmqtys'] : [];

  if ($fid > 0 && !empty($fname) && $fprice > 0) {
    mysqli_begin_transaction($conn);
    try {
      // 1. Update core text details
      $upd_stmt = $conn->prepare("UPDATE Furnitures SET fname = ?, fdesc = ?, fprice = ? WHERE fid = ?");
      $upd_stmt->bind_param("ssdi", $fname, $fdesc, $fprice, $fid);
      $upd_stmt->execute();
      $upd_stmt->close();

      // 2. Handle Optional Image Replacement (Only upload if a new file is chosen)
      if (isset($_FILES['edit_fimage']) && $_FILES['edit_fimage']['error'] == 0) {
        $target_dir = "../Sample Furniture Images/";
        $target_file = $target_dir . $fid . ".png";
        move_uploaded_file($_FILES['edit_fimage']['tmp_name'], $target_file);
      }

      // 3. Clear out historical materials map records to allow safe clean override updates
      $del_mat = $conn->prepare("DELETE FROM FurnitureMaterials WHERE fid = ?");
      $del_mat->bind_param("i", $fid);
      $del_mat->execute();
      $del_mat->close();

      // 4. Re-insert newly configured relational records
      if (!empty($posted_mids)) {
        for ($i = 0; $i < count($posted_mids); $i++) {
          $mid = intval($posted_mids[$i]);
          $pmqty = intval($posted_pmqtys[$i]);

          if ($pmqty > 0) {
            $mat_stmt = $conn->prepare("INSERT INTO FurnitureMaterials (fid, mid, pmqty) VALUES (?, ?, ?)");
            $mat_stmt->bind_param("iii", $fid, $mid, $pmqty);
            $mat_stmt->execute();
            $mat_stmt->close();
          }
        }
      }

      mysqli_commit($conn);
      $success_message = "Furniture product #" . $fid . " updates successfully committed.";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Update processing failure! Failed to save changes.";
    }
  } else {
    $error_message = "Invalid input data detected! Please verify modification fields.";
  }
}

// Handle Secure Furniture Record Deletion with Order Constraints and Image Cleanup
if (isset($_GET['delete_id'])) {
  $delete_fid = intval($_GET['delete_id']);

  $check_stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM OrderFurnitures WHERE fid = ?");
  $check_stmt->bind_param("i", $delete_fid);
  $check_stmt->execute();
  $check_res = $check_stmt->get_result()->fetch_assoc();
  $check_stmt->close();

  if ($check_res['total_orders'] > 0) {
    $error_message = "Deletion Denied! This furniture product cannot be deleted because active customer order records exist for it.";
  } else {
    mysqli_begin_transaction($conn);
    try {
      $del_relations = $conn->prepare("DELETE FROM FurnitureMaterials WHERE fid = ?");
      $del_relations->bind_param("i", $delete_fid);
      $del_relations->execute();
      $del_relations->close();

      $del_item = $conn->prepare("DELETE FROM Furnitures WHERE fid = ?");
      $del_item->bind_param("i", $delete_fid);
      $del_item->execute();
      $del_item->close();

      mysqli_commit($conn);
      $success_message = "Furniture product #" . $delete_fid . " has been removed from the catalog.";

      $target_image = "../Sample Furniture Images/" . $delete_fid . ".png";
      if ($delete_fid != 1 && file_exists($target_image)) {
        unlink($target_image);
      }
    } catch (Exception $e) {
      mysqli_rollback($conn);
      $error_message = "Database processing error during deletion execution loop.";
    }
  }
}

// Fetch available active stock materials raw logs
$materials_query = "SELECT mid, mname, munit FROM Materials ORDER BY mid ASC";
$materials_result = mysqli_query($conn, $materials_query);

// 🌐 Modified Query: Added a second GROUP_CONCAT to extract precise numerical combinations (mid:qty) for quick JS form mapping
$furniture_query = "SELECT f.fid, f.fname, f.fdesc, f.fprice, 
                    IFNULL(GROUP_CONCAT(CONCAT(m.mname, ' x ', fm.pmqty) SEPARATOR ', '), 'No materials configured') AS materials_used,
                    IFNULL(GROUP_CONCAT(CONCAT(fm.mid, ':', fm.pmqty)), '') AS raw_materials
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
  <link rel="stylesheet" href="staff_style.css">
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
      <form action="manage_furniture.php" method="POST" enctype="multipart/form-data">
        <div class="form-row">
          <div class="form-group name-group">
            <label>Furniture Name</label>
            <input type="text" name="fname" placeholder="e.g. Modern Sofa" required>
          </div>
          <div class="form-group price-group">
            <label>Price ($)</label>
            <input type="number" name="fprice" min="0" step="0.01" placeholder="0.00" required>
          </div>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="fdesc" rows="3" placeholder="Enter product description..."></textarea>
        </div>

        <div class="form-group">
          <label>Furniture Image</label>
          <input type="file" name="fimage" accept="image/*" required>
        </div>

        <div class="form-group">
          <label>Material Composition</label>
          <div id="selected-materials-display">
            <span style="color: #95a5a6; font-size: 14px;">No materials selected. Click the button below to
              configure...</span>
          </div>
        </div>

        <div class="form-actions-row">
          <button type="button" class="btn btn-success btn-large" onclick="openModal()">🔍 Select & Configure
            Materials</button>
          <button type="submit" name="add_furniture" class="btn btn-primary btn-large">➕ Add to Catalog</button>
        </div>
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
              $image_path = "../Sample Furniture Images/" . $f_row['fid'] . ".png";
              if (!file_exists($image_path)) {
                $image_path = "../Sample Furniture Images/1.png";
              }
              ?>
              <tr>
                <td>#<?php echo str_pad($f_row['fid'], 3, '0', STR_PAD_LEFT); ?></td>
                <td><img src="<?php echo $image_path; ?>" class="thumbnail" alt="Furniture Image"></td>
                <td><strong><?php echo htmlspecialchars($f_row['fname']); ?></strong><br><small
                    style="color:#7f8c8d;"><?php echo htmlspecialchars($f_row['fdesc']); ?></small></td>
                <td class="price-tag">$<?php echo number_format($f_row['fprice'], 2); ?></td>
                <td><small><?php echo htmlspecialchars($f_row['materials_used']); ?></small></td>
                <td>
                  <button type="button" class="btn btn-warning" style="padding: 6px 12px; font-size:13px; margin-right:5px;"
                    data-fid="<?php echo $f_row['fid']; ?>"
                    data-fname="<?php echo htmlspecialchars($f_row['fname'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-fprice="<?php echo $f_row['fprice']; ?>"
                    data-fdesc="<?php echo htmlspecialchars($f_row['fdesc'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-raw-materials="<?php echo htmlspecialchars($f_row['raw_materials'], ENT_QUOTES, 'UTF-8'); ?>"
                    onclick="triggerFurnitureEditModal(this)">✏️ Edit</button>
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
          <thead style="position: sticky; top: 0; background: white; z-index: 10;">
            <tr>
              <th>Select</th>
              <th>Material Name</th>
              <th>Required Qty</th>
              <th>Unit</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($materials_result)):
              mysqli_data_seek($materials_result, 0); // Reset query pointer array location back to zero index
              while ($m_row = mysqli_fetch_assoc($materials_result)): ?>
                <tr>
                  <td><input type="checkbox" class="mat-check" value="<?php echo $m_row['mid']; ?>"
                      data-name="<?php echo htmlspecialchars($m_row['mname'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                  <td><?php echo htmlspecialchars($m_row['mname']); ?></td>
                  <td><input type="number" class="mat-qty" placeholder="0" min="1" value="1"></td>
                  <td><?php echo htmlspecialchars($m_row['munit']); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div style="margin-top: 25px; text-align: right;">
        <button type="button" class="btn" style="background:#eee; color:#333;" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="confirmSelection()">Confirm</button>
      </div>
    </div>
  </div>

  <div id="editFurnitureModal" class="modal">
    <div class="modal-content" style="max-width: 650px; margin: 4% auto;">
      <form action="manage_furniture.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="edit-fid" name="fid">

        <div class="modal-header">
          <h3>Edit Furniture Details <span id="edit-title-id" style="color:#3498db;"></span></h3>
          <span class="close-btn" onclick="closeFurnitureEditModal()">&times;</span>
        </div>

        <div style="max-height: 480px; overflow-y: auto; padding-right: 5px;">
          <div class="form-row">
            <div class="form-group name-group">
              <label>Furniture Name</label>
              <input type="text" id="edit-fname" name="fname" required>
            </div>
            <div class="form-group price-group">
              <label>Price ($)</label>
              <input type="number" id="edit-fprice" name="fprice" min="0" step="0.01" required>
            </div>
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea id="edit-fdesc" name="fdesc" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label>Replace Image <small style="color:#7f8c8d; font-weight:normal;">(Leave blank to keep current
                image)</small></label>
            <input type="file" name="edit_fimage" accept="image/*">
          </div>

          <div class="form-group">
            <label style="margin-bottom: 12px; color:#2c3e50;">Configure Recipe Composition</label>
            <div style="border: 1px solid #ddd; border-radius: 6px; max-height: 200px; overflow-y: auto;">
              <table style="width: 100%; margin:0;">
                <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 5;">
                  <tr>
                    <th style="padding:10px;">Select</th>
                    <th style="padding:10px;">Material</th>
                    <th style="padding:10px;">Required Qty</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if (!empty($materials_result)):
                    mysqli_data_seek($materials_result, 0); // Reset query matrix pointer again for the second modal list output loop
                    while ($m_row = mysqli_fetch_assoc($materials_result)): ?>
                      <tr>
                        <td style="padding:10px;"><input type="checkbox" name="edit_mids[]" class="edit-mat-check"
                            value="<?php echo $m_row['mid']; ?>" id="edit-cb-<?php echo $m_row['mid']; ?>"></td>
                        <td style="padding:10px;"><label style="font-weight:normal; cursor:pointer;"
                            for="edit-cb-<?php echo $m_row['mid']; ?>"><?php echo htmlspecialchars($m_row['mname']); ?>
                            <small style="color:#95a5a6;">(<?php echo $m_row['munit']; ?>)</small></label></td>
                        <td style="padding:10px;"><input type="number" name="edit_pmqtys[]" class="edit-mat-qty"
                            id="edit-qty-<?php echo $m_row['mid']; ?>" min="1" value="1" style="width: 70px; padding:4px;">
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div style="margin-top: 25px; text-align: right; border-top: 1px solid #eee; padding-top:15px;">
          <button type="button" class="btn" style="background:#eee; color:#333;"
            onclick="closeFurnitureEditModal()">Cancel</button>
          <button type="submit" name="edit_furniture" class="btn btn-primary" style="padding: 10px 30px;">💾 Save
            Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script src="./script.js"></script>
</body>

</html>