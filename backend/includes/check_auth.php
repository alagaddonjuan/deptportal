<?php
require_once __DIR__ . '/../config/auth.php';

function authenticate() {
    session_start();
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return $_SESSION['user'];
}

// Usage in protected endpoints:
// require_once __DIR__ . '/includes/check_auth.php';
// $user = authenticate();
?>