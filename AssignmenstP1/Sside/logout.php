<?php
// Initialize session data tracking
session_start();

// Clear all active session variables
$_SESSION = array();

// Fully destroy the session container
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// Redirect back to the entry login gateway
header("Location: staff_login.php");
exit();
?>