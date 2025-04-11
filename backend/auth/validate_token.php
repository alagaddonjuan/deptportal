<?php
require_once __DIR__ . '/../config/db.php';

$token = $_GET['token'] ?? '';

// 1. Check token validity
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired token");
}

// 2. Show password reset form
echo '
<form action="reset_password.php" method="POST">
    <input type="hidden" name="token" value="'.$token.'">
    <input type="password" name="new_password" required>
    <button type="submit">Reset Password</button>
</form>';
?>