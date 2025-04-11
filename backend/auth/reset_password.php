<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

// ========================
// 1. HTTPS ENFORCEMENT
// ========================
if ($_SERVER['HTTPS'] != 'on' && $_SERVER['HTTP_HOST'] != 'localhost') {
    header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    exit;
}

// ========================
// 2. INPUT VALIDATION
// ========================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($_POST['token']) || empty($_POST['new_password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Token and new password required']);
    exit;
}

$token = htmlspecialchars($_POST['token']);
$newPassword = $_POST['new_password'];

// ========================
// 3. PASSWORD COMPLEXITY
// ========================
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Password must contain:',
        'requirements' => [
            'Minimum 8 characters',
            'At least 1 uppercase letter',
            'At least 1 number',
            'At least 1 special character'
        ]
    ]);
    exit;
}

// ========================
// 4. TOKEN VERIFICATION
// ========================
try {
    $stmt = $conn->prepare("SELECT id, reset_expiry FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    if (strtotime($user['reset_expiry']) < time()) {
        http_response_code(400);
        echo json_encode(['error' => 'Token expired']);
        exit;
    }

    // ========================
    // 5. PASSWORD UPDATE
    // ========================
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    // ========================
    // 6. SECURITY CLEANUP
    // ========================
    // Invalidate all sessions (optional)
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    echo json_encode(['message' => 'Password updated successfully']);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database operation failed']);
}
?>