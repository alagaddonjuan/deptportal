<?php
function authenticate() {
    session_start();
    
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    return $_SESSION['user'];
}
?>