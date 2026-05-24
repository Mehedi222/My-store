<?php
// logout.php - Destroys session and logs out user
require_once 'config.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Start a new session just to flash the success message
session_start();
$_SESSION['alert_success'] = "You have successfully logged out.";
header("Location: login.php");
exit;
?>
