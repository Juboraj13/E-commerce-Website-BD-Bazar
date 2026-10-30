<?php
/**
 * logout.php — User Logout Handler
 * Clears authentication session, regenerates ID and redirects to login with flash notice.
 */
require_once __DIR__ . '/backend/config/config.php';

if (is_logged_in()) {
    unset($_SESSION['user']);
}

// Clear session securely
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Start a new clean session for the flash message
session_start();
session_regenerate_id(true);

set_flash('success', 'You have been safely signed out. See you again soon!');
redirect(asset_url('login.php'));

