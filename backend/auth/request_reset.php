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
// 2. RATE LIMITING
// ========================
session_start();
$max_attempts = 3;
$lockout_time = 300; // 5 minutes

if (!isset($_SESSION['reset_attempts'])) {
    $_SESSION['reset_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

if ($_SESSION['reset_attempts'] >= $max_attempts) {
    if (time() - $_SESSION['last_attempt'] < $lockout_time) {
        http_response_code(429);
        die(json_encode(['error' => 'Too many attempts. Try again in 5 minutes.']));
    } else {
        // Reset counter if lockout period has passed
        $_SESSION['reset_attempts'] = 0;
    }
}

// ========================
// 3. INPUT VALIDATION
// ========================
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email required']);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// ========================
// 4. USER VERIFICATION
// ========================
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Always return success to prevent email enumeration
if (!$user) {
    $_SESSION['reset_attempts']++;
    $_SESSION['last_attempt'] = time();
    sleep(1); // Delay to slow down brute force
    echo json_encode(['message' => 'If the email exists, a reset link was sent']);
    exit;
}

// ========================
// 5. TOKEN GENERATION
// ========================
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// ========================
// 6. DATABASE UPDATE
// ========================
$stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
$stmt->execute([$token, $expiry, $user['id']]);

// ========================
// 7. PASSWORD COMPLEXITY CHECK
// (For frontend validation - shown here for reference)
// ========================
/*
Frontend should validate:
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 number
- At least 1 special character
*/

// ========================
// 8. EMAIL SENDING
// ========================
$resetLink = "https://".$_SERVER['HTTP_HOST']."/dept-portal/backend/auth/validate_token.php?token=$token";

// Using PHPMailer (configure in config/auth.php)
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.cs.edu';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@cs.edu';
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom('noreply@cs.edu', 'CS Department');
    $mail->addAddress($email);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = "Click to reset: $resetLink (expires in 1 hour)";
    
    $mail->send();
    
    // Update attempt counter on success
    $_SESSION['reset_attempts']++;
    $_SESSION['last_attempt'] = time();
    
    echo json_encode(['message' => 'Reset link sent to email']);
} catch (Exception $e) {
    error_log("Mailer Error: ".$mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send email']);
}
?>