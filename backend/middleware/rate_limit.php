session_start();
$_SESSION['api_requests'] = ($_SESSION['api_requests'] ?? 0) + 1;

if ($_SESSION['api_requests'] > 100) {
    http_response_code(429);
    die('Too many requests');
}