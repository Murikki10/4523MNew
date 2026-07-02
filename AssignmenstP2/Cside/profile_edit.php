<?php
session_start();

if (!isset($_SESSION['cust_id'])) {
  header("Location: login.php");
  exit();
}

require_once('../conn.php');

if (!isset($conn)) {
  die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

$cid = $_SESSION['cust_id'];
$success_message = '';
$error_message = '';
$active_tab = 'profile';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (isset($_POST['update_profile'])) {
    $active_tab = 'profile';
    $new_name = trim($_POST['cname']);
    $new_tel = trim($_POST['ctel']);

    if (!empty($new_name)) {
      $check_stmt = $conn->prepare("SELECT cid FROM Customers WHERE cname = ? AND cid != ? LIMIT 1");
      $check_stmt->bind_param("si", $new_name, $cid);
      $check_stmt->execute();
      $check_res = $check_stmt->get_result();
      $check_stmt->close();

      if ($check_res && $check_res->num_rows > 0) {
        $error_message = "Failure: The Name '" . htmlspecialchars($new_name) . "' is already taken by another member.";
      } else {

        $up_stmt = $conn->prepare("UPDATE Customers SET cname = ?, ctel = ? WHERE cid = ?");
        $up_stmt->bind_param("ssi", $new_name, $new_tel, $cid);
        if ($up_stmt->execute()) {
          $_SESSION['cust_name'] = $new_name;
          $success_message = "Success: Personal Information saved updates successfully.";
        } else {
          $error_message = "Failure: Database processing error during profile update.";
        }
        $up_stmt->close();
      }
    } else {
      $error_message = "Failure: Full Name cannot be left completely empty.";
    }
  }

  if (isset($_POST['update_address'])) {
    $active_tab = 'address';
    $new_addr = trim($_POST['caddr']);

    if (!empty($new_addr)) {
      $up_stmt = $conn->prepare("UPDATE Customers SET caddr = ? WHERE cid = ?");
      $up_stmt->bind_param("si", $new_addr, $cid);
      if ($up_stmt->execute()) {
        $success_message = "Success: Shipping Address updated successfully for future collections.";
      } else {
        $error_message = "Failure: Database processing error during address update.";
      }
      $up_stmt->close();
    } else {
      $error_message = "Failure: Shipping Address field is required.";
    }
  }


  if (isset($_POST['update_password'])) {
    $active_tab = 'security';
    $current_pass = trim($_POST['current_password']);
    $new_pass = trim($_POST['new_password']);
    $confirm_pass = trim($_POST['confirm_password']);

    if (!empty($current_pass) && !empty($new_pass) && !empty($confirm_pass)) {
      if ($new_pass !== $confirm_pass) {
        $error_message = "Failure: New password entries do not match. Please verify.";
      } else {

        $pwd_stmt = $conn->prepare("SELECT cpassword FROM Customers WHERE cid = ? LIMIT 1");
        $pwd_stmt->bind_param("i", $cid);
        $pwd_stmt->execute();
        $pwd_row = $pwd_stmt->get_result()->fetch_assoc();
        $pwd_stmt->close();

        if ($current_pass === $pwd_row['cpassword'] || password_verify($current_pass, $pwd_row['cpassword'])) {
          $up_stmt = $conn->prepare("UPDATE Customers SET cpassword = ? WHERE cid = ?");
          $up_stmt->bind_param("si", $new_pass, $cid);
          if ($up_stmt->execute()) {
            $success_message = "Success: Account security credentials updated safely.";
          } else {
            $error_message = "Failure: Database processing error during password reset.";
          }
          $up_stmt->close();
        } else {
          $error_message = "Failure: Current Password is incorrect. Authentication failed.";
        }
      }
    } else {
      $error_message = "Failure: All password tracking fields are mandatory.";
    }
  }
}

$cust_stmt = $conn->prepare("SELECT cname, ctel, caddr FROM Customers WHERE cid = ? LIMIT 1");
$cust_stmt->bind_param("i", $cid);
$cust_stmt->execute();
$cust_data = $cust_stmt->get_result()->fetch_assoc();
$cust_stmt->close();

$db_name = isset($cust_data['cname']) ? $cust_data['cname'] : '';
$db_tel = isset($cust_data['ctel']) ? $cust_data['ctel'] : '';
$db_addr = isset($cust_data['caddr']) ? $cust_data['caddr'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings | Premium Living</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="staff/staff_style.css">
  <link rel="stylesheet" href="cust_style.css">
  <style>
    .alert {
      position: fixed;
      bottom: 25px;
      right: 25px;
      z-index: 99999;
      min-width: 320px;
      max-width: 450px;
      padding: 16px 20px;
      border-radius: 8px;
      font-weight: bold;
      font-size: 14px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
      gap: 12px;
      opacity: 0;
      transform: translateX(50px);
      transition: opacity 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .alert.toast-show {
      opacity: 1;
      transform: translateX(0);
    }

    .alert.toast-hide {
      opacity: 0;
      transform: translateY(-20px);
      transition: opacity 0.4s ease, transform 0.4s ease;
    }

    .alert-success {
      background: #ffffff;
      color: #1e293b;
      border-left: 5px solid #2ecc71;
    }

    .alert-danger {
      background: #ffffff;
      color: #1e293b;
      border-left: 5px solid #e74c3c;
    }
  </style>
</head>

<body class="pl-settings-body">

  <nav class="top-nav">
    <a href="index.php" class="logo">Premium<span>Living</span></a>
    <div style="font-size: 14px; color: #7f8c8d;">Signed in as
      <strong><?php echo htmlspecialchars($_SESSION['cust_name']); ?></strong></div>
  </nav>

  <?php if ($success_message != '')
    echo "<div class='alert alert-success'>$success_message</div>"; ?>
  <?php if ($error_message != '')
    echo "<div class='alert alert-danger'>$error_message</div>"; ?>

  <div class="main-wrapper">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h3>Account Settings</h3>
      </div>
      <nav class="sidebar-menu">
        <div class="menu-item <?php echo ($active_tab === 'profile') ? 'active' : ''; ?>" data-tab="profile"><i>👤</i>
          Personal Info</div>
        <div class="menu-item <?php echo ($active_tab === 'address') ? 'active' : ''; ?>" data-tab="address"><i>📍</i>
          Shipping Address</div>
        <div class="menu-item <?php echo ($active_tab === 'security') ? 'active' : ''; ?>" data-tab="security"><i>🔒</i>
          Password & Security</div>
        <a href="dashboard.php" class="menu-item"
          style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;"><i>📦</i> Back to Dashboard</a>
      </nav>
    </aside>

    <div style="flex-grow: 1;">

      <!-- SECTION 1: 👤 PERSONAL INFO TAB -->
      <div id="profile" class="tab-content <?php echo ($active_tab === 'profile') ? 'active' : ''; ?>">
        <div class="content-card">
          <div class="content-header">
            <h2>Personal Information</h2>
            <p>Manage your basic account details and contact verification parameters.</p>
          </div>
          <form action="profile_edit.php" method="POST">
            <div class="form-grid">
              <div class="form-group full">
                <label>Full Account Name *</label>
                <input type="text" name="cname" value="<?php echo htmlspecialchars($db_name); ?>" required>
              </div>
              <div class="form-group">
                <label>Contact Phone Number</label>
                <input type="tel" name="ctel" value="<?php echo htmlspecialchars($db_tel); ?>"
                  placeholder="+852 9000 0000">
              </div>
              <div class="form-group">
                <label>Account Level Tier</label>
                <input type="text" value="Premium Collector" readonly
                  style="background:#eee; color:#7f8c8d; cursor:not-allowed;">
              </div>
            </div>
            <button type="submit" name="update_profile" class="btn-save">Save Profile</button>
          </form>
        </div>
      </div>

      <!-- SECTION 2: 📍 SHIPPING ADDRESS TAB -->
      <div id="address" class="tab-content <?php echo ($active_tab === 'address') ? 'active' : ''; ?>">
        <div class="content-card">
          <div class="content-header">
            <h2>Shipping Address</h2>
            <p>Your primary delivery location for all bespoke artisan furniture orders.</p>
          </div>
          <form action="profile_edit.php" method="POST">
            <div class="form-grid single-col">
              <div class="form-group">
                <label>Full Logistics Delivery Address *</label>
                <input type="text" name="caddr" value="<?php echo htmlspecialchars($db_addr); ?>"
                  placeholder="Flat, Floor, Building Name, Street, District" required>
              </div>
            </div>
            <button type="submit" name="update_address" class="btn-save">Update Address</button>
          </form>
        </div>
      </div>

      <!-- SECTION 3: 🔒 PASSWORD & SECURITY TAB -->
      <div id="security" class="tab-content <?php echo ($active_tab === 'security') ? 'active' : ''; ?>">
        <div class="content-card">
          <div class="content-header">
            <h2>Security & Password</h2>
            <p>Ensure your boutique collection account remains guarded with a robust key credential.</p>
          </div>
          <form action="profile_edit.php" method="POST" id="pl-security-form">
            <div class="form-grid single-col">
              <div class="form-group">
                <label>Current Credentials Password *</label>
                <input type="password" name="current_password" placeholder="••••••••" required>
              </div>
              <div class="form-group">
                <label>New Strong Password *</label>
                <input type="password" name="new_password" id="pl-new-password"
                  placeholder="Enter brand new password key" required>
              </div>
              <div class="form-group">
                <label>Confirm New Password *</label>
                <input type="password" name="confirm_password" id="pl-confirm-password"
                  placeholder="Re-type new password key" required>
              </div>
            </div>
            <button type="submit" name="update_password" class="btn-save"
              style="background: var(--accent-color);">Update Password</button>
          </form>
        </div>
      </div>

    </div>
  </div>

  <script src="cust_script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const alerts = document.querySelectorAll('.pl-settings-body .alert');
      alerts.forEach(function (alert) {
        setTimeout(function () { alert.classList.add('toast-show'); }, 50);
        setTimeout(function () {
          alert.classList.remove('toast-show');
          alert.classList.add('toast-hide');
          setTimeout(function () { alert.remove(); }, 400);
        }, 3000);
      });
    });
  </script>
</body>

</html>