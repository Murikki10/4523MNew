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
$active_panel = 'login'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cust_login'])) {
    $active_panel = 'login';
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
                $error_message = "Failure: Invalid password! Please double check your entries.";
            }
        } else {
            $error_message = "Failure: Customer Name not found! Please register first.";
        }
        $stmt->close();
    } else {
        $error_message = "Failure: Please fill in all mandatory fields.";
    }
}

// 🚀 處理註冊 (Name Register + Dummy Defs)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cust_register'])) {
    $active_panel = 'register'; 
    $cname = trim($_POST['reg_cname']);
    $password = trim($_POST['reg_cpassword']);
    $confirm_password = trim($_POST['reg_cconfirm_password']);

    if (!empty($cname) && !empty($password) && !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $error_message = "Failure: Passwords do not match! Please check your typing.";
        } else {
            $check_stmt = $conn->prepare("SELECT cid FROM Customers WHERE cname = ? LIMIT 1");
            $check_stmt->bind_param("s", $cname);
            $check_stmt->execute();
            $check_res = $check_stmt->get_result();
            $check_stmt->close();

            if ($check_res && $check_res->num_rows > 0) {
                $error_message = "Failure: The Name '" . htmlspecialchars($cname) . "' is already taken! Please choose another name.";
            } else {
                $dummy_ctel = ''; 
                $dummy_caddr = 'Pending Update';

                $ins_stmt = $conn->prepare("INSERT INTO Customers (cname, cpassword, ctel, caddr) VALUES (?, ?, ?, ?)");
                $ins_stmt->bind_param("ssss", $cname, $password, $dummy_ctel, $dummy_caddr);

                if ($ins_stmt->execute()) {
                    $success_message = "Success: Account registered successfully! You can login now.";
                    $active_panel = 'login'; 
                } else {
                    $error_message = "Failure: Database processing error during registration.";
                }
                $ins_stmt->close();
            }
        }
    } else {
        $error_message = "Failure: Name and both Password fields are mandatory.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Access Portal - Premium Living</title>
  <link rel="stylesheet" href="staff/staff_style.css">
  <link rel="stylesheet" href="cust_style.css">
</head>
<body class="pl-auth-wrapper">

  <?php if ($success_message != '') echo "<div class='alert alert-success'>$success_message</div>"; ?>
  <?php if ($error_message != '') echo "<div class='alert alert-danger'>$error_message</div>"; ?>

  <div class="login-container">
    
    <div id="login-panel" style="display: <?php echo ($active_panel === 'login') ? 'block' : 'none'; ?>;">
      <div class="login-header">
        <h2>Premium Living</h2>
        <p>Welcome back! Please login with your name</p>
      </div>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label>Customer Name</label>
          <input type="text" name="cname" placeholder="Enter your registered name" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="cpassword" placeholder="••••••••" required>
        </div>
        <button type="submit" name="cust_login" class="btn-action">Secure Login 🔐</button>
      </form>

      <div class="toggle-link">
        Don't have an account? <button type="button" id="pl-to-register-btn">Create Account Now</button>
      </div>
    </div>

    <div id="register-panel" style="display: <?php echo ($active_panel === 'register') ? 'block' : 'none'; ?>;">
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
          <input type="password" id="reg-confirm-password" name="reg_cconfirm_password" placeholder="Re-type your password" required>
        </div>
        
        <button type="submit" name="cust_register" class="btn-action" style="background: #2ecc71;">🚀 Register Now</button>
      </form>

      <div class="toggle-link">
        Already have an account? <button type="button" id="pl-to-login-btn">Back to Login</button>
      </div>
    </div>

  </div>

  <script src="cust_script.js"></script>
</body>
</html>