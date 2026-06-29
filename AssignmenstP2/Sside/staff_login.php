<?php
// Start the session to track user login status
session_start();

// Include database connection (adjust path based on folder structure)
require_once('../conn.php');

$error_message = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sname = $_POST['sname'];
    $spassword = $_POST['spassword'];

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
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Premium Living Furniture</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 320px;
        }

        .login-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #0056b3;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #0056b3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn:hover {
            background: #004494;
        }

        .error {
            color: #d9534f;
            background: #fdf7f7;
            border: 1px solid #d9534f;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Staff Portal Login</h2>

        <?php if ($error_message != '') {
            echo "<div class='error'>$error_message</div>";
        } ?>

        <form method="POST" action="staff_login.php" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="sname">Staff Name:</label>
                <input type="text" id="sname" name="sname" placeholder="Enter staff name" required>
            </div>
            <div class="form-group">
                <label for="spassword">Password:</label>
                <input type="password" id="spassword" name="spassword" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>

    <script>
        // Frontend input validation
        function validateForm() {
            var sname = document.getElementById('sname').value;
            var spassword = document.getElementById('spassword').value;

            if (sname.trim() === "" || spassword.trim() === "") {
                alert("Please fill in all fields!");
                return false;
            }
            return true;
        }
    </script>

</body>

</html>