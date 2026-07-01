<?php
// Start the session to track user login status
session_start();

// Include database connection (adjust path based on folder structure)
require_once('../conn.php');

$error_message = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sname = trim($_POST['sname']);
    $spassword = $_POST['spassword'];

    if (!empty($sname) && !empty($spassword)) {
        // Use prepared statements to prevent SQL Injection
        $stmt = $conn->prepare("SELECT sid, sname, srole FROM Staffs WHERE sname = ? AND spassword = ?");
        $stmt->bind_param("ss", $sname, $spassword);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch matching staff record
            $row = $result->fetch_assoc();

            // Store user details in session variables
            $_SESSION['staff_id'] = $row['sid'];
            $_SESSION['staff_name'] = $row['sname'];
            $_SESSION['staff_role'] = $row['srole'];

            // Redirect to staff dashboard page
            header("Location: staff_index.php");
            exit();
        } else {
            // Set error message if login credentials are invalid
            $error_message = "Login failed! Invalid username or password.";
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all mandatory fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Premium Living Furniture</title>
    <link rel="stylesheet" href="staff_style.css">
</head>

<body class="login-body">

    <div class="login-container">
        <div class="brand-logo">Premium <span>Living</span></div>
        <h2>Staff Portal Login</h2>

        <?php if ($error_message != ''): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="staff_login.php" onsubmit="return handleFormSubmit()">
            <div class="form-group">
                <label for="sname">Staff Name:</label>
                <input type="text" id="sname" name="sname" placeholder="Enter staff name" required>
            </div>
            <div class="form-group">
                <label for="spassword">Password:</label>
                <input type="password" id="spassword" name="spassword" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="footer-links">
            <p>Forgot password? <a href="#">Contact IT Admin</a></p>
            <p><a href="../Cside/index.php">← Return to Main Website</a></p>
        </div>
    </div>
    <script src="script.js"></script>

</body>

</html>