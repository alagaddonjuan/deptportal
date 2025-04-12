// backend/api/enrollments.php
<?php
require_once __DIR__ . '/../middleware/auth_check.php';
require_once __DIR__ . '/../../includes/db_operations.php';

header('Content-Type: application/json');

class EnrollmentAPI extends DBOperations {
    public function enrollStudent() {
        $user_id = $_SESSION['user_id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['course_id'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'Course ID required']));
        }
        
        try {
            // Check if already enrolled
            $stmt = $this->executeQuery(
                "SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?",
                [$user_id, $data['course_id']]
            );
            
            if ($stmt->get_result()->num_rows > 0) {
                http_response_code(409);
                exit(json_encode(['error' => 'Already enrolled in this course']));
            }
            
            $this->executeQuery(
                "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)",
                [$user_id, $data['course_id']]
            );
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$enrollmentAPI = new EnrollmentAPI();

if ($method === 'POST') {
    $enrollmentAPI->enrollStudent();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>