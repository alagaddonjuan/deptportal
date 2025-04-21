<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// Security headers
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('X-Content-Type-Options: nosniff');

// ========================
// 1. INPUT VALIDATION (Enhanced)
// ========================
$jsonPayload = file_get_contents('php://input');

if (empty($jsonPayload)) {
    http_response_code(400);
    die(json_encode(['error' => 'Request body cannot be empty']));
}

$data = json_decode($jsonPayload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode([
        'error' => 'Invalid JSON data',
        'details' => json_last_error_msg()
    ]));
}

// ========================
// 2. RATE LIMITING (From Your Version)
// ========================
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

$max_attempts = 5;
$lockout_time = 300;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

if ($_SESSION['login_attempts'] >= $max_attempts) {
    if (time() - $_SESSION['last_attempt'] < $lockout_time) {
        http_response_code(429);
        die(json_encode([
            'error' => 'Too many attempts',
            'retry_after' => $lockout_time - (time() - $_SESSION['last_attempt'])
        ]));
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

// ========================
// 3. AUTHENTICATION (Combined Best)
// ========================
try {
    $stmt = $conn->prepare("
        SELECT id, email, password, role, is_active 
        FROM users 
        WHERE email = ? 
        LIMIT 1
    ");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        http_response_code(401);
        die(json_encode(['error' => 'Invalid credentials']));
    }

    if (!$user['is_active']) {
        http_response_code(403);
        die(json_encode(['error' => 'Account deactivated']));
    }

    // ========================
    // 4. SESSION SETUP
    // ========================
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];

    $_SESSION['login_attempts'] = 0;

    // ========================
    // 5. RESPONSE
    // ========================
    echo json_encode([
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'session_id' => session_id()
    ]);

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database error']));
}

// In login.php after successful authentication
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

?>