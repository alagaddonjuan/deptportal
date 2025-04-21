<?php
session_start();

$limit = 100; // Requests per minute
$window = 60; // Seconds

$currentTime = time();
$key = 'api_rate_limit_' . sha1($_SERVER['REMOTE_ADDR']);

if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = [
        'count' => 0,
        'start' => $currentTime
    ];
}

// Reset counter if window expired
if ($currentTime - $_SESSION[$key]['start'] > $window) {
    $_SESSION[$key] = [
        'count' => 0,
        'start' => $currentTime
    ];
}

// Check limit
if (++$_SESSION[$key]['count'] > $limit) {
    header('Retry-After: ' . ($window - ($currentTime - $_SESSION[$key]['start'])));
    http_response_code(429);
    die(json_encode(['error' => 'Too many requests']));
}
?>