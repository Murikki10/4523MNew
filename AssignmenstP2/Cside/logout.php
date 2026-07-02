<?php
// 1. Initialize the session container to intercept existing secure tokens
session_start();

// 2. Clear all registered session variables into an empty array payload
$_SESSION = array();

// 3. Destruct the session tracking cookie completely inside the client browser if it exists
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

// 4. Destroy the active session registry state permanently on the server side
session_destroy();

// 5. Secure Redirection: Safely route the customer straight back to the login portal page
header("Location: login.php");
exit();
?>