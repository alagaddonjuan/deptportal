<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($data['password'], $user['password'])) {
    // Start session or generate JWT
    session_start();
    $_SESSION['user'] = [
        'id' => $user['id'],
        'role' => $user['role']
    ];
    
    echo json_encode(['token' => generateJWT($user)]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
}

function generateJWT($user) {
    $payload = [
        'iss' => 'dept-portal',
        'sub' => $user['id'],
        'role' => $user['role'],
        'exp' => time() + 3600
    ];
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}
?>