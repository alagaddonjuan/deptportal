<?php
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
?>