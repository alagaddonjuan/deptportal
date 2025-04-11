<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable if using HTTPS
ini_set('session.cookie_samesite', 'Strict');

// JWT Secret Key (generate via: openssl rand -base64 32)
define('JWT_SECRET', 'your_256_bit_secret_here'); 

// Session timeout (1 hour)
define('SESSION_TIMEOUT', 3600);

// User roles
define('ROLES', [
    'student' => 1,
    'professor' => 2,
    'admin' => 3
]);

function sanitize_input($data) {
    return array_map(function($item) {
        return htmlspecialchars(strip_tags($item));
    }, $data);
}
?>