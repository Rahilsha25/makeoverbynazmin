<?php
// Centralized admin session and inactivity timeout handling
// Place this at the top of any admin page that requires authentication.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// timeout in seconds (e.g., 30 minutes)
$timeout = 30 * 60;

// If not logged in, redirect to login
if (empty($_SESSION['admin_user'])) {
    header('Location: login.php');
    exit;
}

// If last activity set, check timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // clear session and redirect to login with timeout flag
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

?>
