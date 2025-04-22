<?php
function verifyCSRFToken() {
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || 
        $_SERVER['REQUEST_METHOD'] === 'PUT' || 
        $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        
        $providedToken = $_POST['csrf_token'] ?? 
                        $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                        null;

        if (!$providedToken || !hash_equals($_SESSION['csrf_token'], $providedToken)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token validation failed']));
        }
        
        // Regenerate token after validation
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>