<?php
// File: backend/auth/logout.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

// ========================
// 1. DESTROY SESSION
// ========================
session_start();

// Unset all session variables
$_SESSION = array();

// Delete session cookie
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

// Destroy session
session_destroy();

// ========================
// 2. INVALIDATE JWT (If using tokens)
// ========================
if (isset($_COOKIE['jwt'])) {
    setcookie('jwt', '', time() - 3600, '/', '', true, true); // Secure + HTTPOnly
}

// ========================
// 3. SECURITY HEADERS
// ========================
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// ========================
// 4. RESPONSE
// ========================
echo json_encode(['message' => 'Logout successful']);
exit;
?>