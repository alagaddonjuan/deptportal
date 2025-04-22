// backend/middleware/csrf.php
<?php
require_once __DIR__ . '/../config/auth.php';

function verifyCSRFToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
        
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? null)) {
            header('HTTP/1.0 403 Forbidden');
            exit(json_encode(['error' => 'Invalid CSRF token']));
        }
    }
}
?>