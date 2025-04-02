<?php
require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Get all users
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
array_walk($users, function(&$user) {
    unset($user['password']);
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare("INSERT INTO users (...) VALUES (...)");
    $stmt->execute([...]);
}

// Add more methods (POST, PUT, DELETE) later
?>