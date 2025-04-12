// backend/middleware/instructor_only.php
<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/auth_functions.php';

function instructorOnly() {
    if (!isLoggedIn()) {
        header('HTTP/1.0 401 Unauthorized');
        exit(json_encode(['error' => 'Authentication required']));
    }
    
    if (!userHasRole('instructor')) {
        header('HTTP/1.0 403 Forbidden');
        exit(json_encode(['error' => 'Instructor access required']));
    }
}
?>