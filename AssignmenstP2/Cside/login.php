<?php
session_start();

if (isset($_SESSION['cust_id'])) {
  header("Location: index.php");
  exit();
}

require_once('../conn.php');

if (!isset($conn)) {
  die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

$success_message = '';
$error_message = '';

// ====================================================================================
// 🚀 BACKEND PROCESSING: LOGIN AND REGISTER CONTROLLERS
// ====================================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cust_login'])) {
  $cname = trim($_POST['cname']);
  $password = trim($_POST['cpassword']);

  if (!empty($cname) && !empty($password)) {
    $stmt = $conn->prepare("SELECT cid, cname, cpassword FROM Customers WHERE cname = ? LIMIT 1");
    $stmt->bind_param("s", $cname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      if ($password === $row['cpassword'] || password_verify($password, $row['cpassword'])) {
        $_SESSION['cust_id'] = $row['cid'];
        $_SESSION['cust_name'] = $row['cname'];
        header("Location: index.php");
        exit();
      } else {
        $error_message = "Failure: Invalid credentials security password key.";
      }
    } else {
      $error_message = "Failure: Assigned Client Login Name does not exist.";
    }
    $stmt->close();
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cust_register'])) {
  $cname = trim($_POST['reg_cname']);
  $password = trim($_POST['reg_cpassword']);
  $confirm_password = trim($_POST['reg_cconfirm_password']);

  if (!empty($cname) && !empty($password)) {
    if ($password !== $confirm_password) {
      $error_message = "Failure: Password entries do not match tracking logs.";
    } else {
      $check_stmt = $conn->prepare("SELECT cid FROM Customers WHERE cname = ? LIMIT 1");
      $check_stmt->bind_param("s", $cname);
      $check_stmt->execute();
      $check_res = $check_stmt->get_result();
      $check_stmt->close();

      if ($check_res && $check_res->num_rows > 0) {
        $error_message = "Failure: Username already registered in our registry collections.";
      } else {
        // Secure database insertion (Uses raw or hashed depending on architecture compatibility)
        $ins_stmt = $conn->prepare("INSERT INTO Customers (cname, cpassword) VALUES (?, ?)");
        $ins_stmt->bind_param("ss", $cname, $password);

        if ($ins_stmt->execute()) {
          $success_message = "Success: Premium Collector profile created. Please log in below.";
        } else {
          $error_message = "Failure: Database transaction failure during client registration.";
        }
        $ins_stmt->close();
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
  <title>Portal | Premium Living</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="staff/staff_style.css">
  <link rel="stylesheet" href="cust_style.css">
  <style>
    /* 🌐 Enhanced CSS structure ensuring smooth fade transitions between active form panels */
    .pl-auth-wrapper .form-panel {
      display: none;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .pl-auth-wrapper .form-panel.active {
      display: block;
      opacity: 1;
    }

    .toggle-link button {
      background: none;
      border: none;
      color: #3498db;
      font-weight: 600;
      cursor: pointer;
      text-decoration: underline;
      font-size: 14px;
      padding: 0;
      margin-left: 5px;
    }

    .toggle-link button:hover {
      color: #2980b9;
    }
  </style>
</head>

<body class="pl-auth-body">

  <nav class="auth-nav">
    <a href="index.php" class="logo">Premium<span>Living</span></a>
  </nav>

  <div class="pl-auth-wrapper">
    <?php if ($success_message != ''): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message != ''): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div id="loginForm" class="form-panel active">
      <div class="login-header">
        <h2>Client Portal</h2>
        <p>Access your curated furniture collections</p>
      </div>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label>Login Name *</label>
          <input type="text" name="cname" placeholder="Enter your registered name" required>
        </div>
        <div class="form-group">
          <label>Secure Key Password *</label>
          <input type="password" name="cpassword" placeholder="••••••••" required>
        </div>
        <button type="submit" name="cust_login" class="btn-action">Login</button>
      </form>

      <div class="toggle-link">
        Don't have an account? <button type="button" id="pl-trigger-to-register">Create Account Now</button>
      </div>
    </div>

    <div id="registerForm" class="form-panel">
      <div class="login-header">
        <h2>Join Premium Living</h2>
        <p>Create a new account with your name</p>
      </div>

      <form method="POST" action="login.php" id="pl-register-form">
        <div class="form-group">
          <label>Choose Login Name *</label>
          <input type="text" name="reg_cname" placeholder="Create your unique login name" required>
        </div>
        <div class="form-group">
          <label>Secure Password *</label>
          <input type="password" id="reg-password" name="reg_cpassword" placeholder="Min 6 characters" required>
        </div>
        <div class="form-group">
          <label>Confirm Password *</label>
          <input type="password" id="reg-confirm-password" name="reg_cconfirm_password"
            placeholder="Re-type your password" required>
        </div>

        <button type="submit" name="cust_register" class="btn-action" style="background: #2ecc71;">Register Now</button>
      </form>

      <div class="toggle-link">
        Already registered? <button type="button" id="pl-trigger-to-login">Sign In Here</button>
      </div>
    </div>

  </div>

  <script src="cust_script.js"></script>
</body>

</html>