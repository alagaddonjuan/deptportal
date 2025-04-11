<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Validation
if(empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password required']);
    exit;
}

// Hash password
$hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
$stmt->execute([
    $data['email'],
    $hashedPassword,
    $data['role'] ?? 'student' // Default role
]);

echo json_encode(['message' => 'Registration successful']);
?>