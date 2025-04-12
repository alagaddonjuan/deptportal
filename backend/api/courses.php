// backend/api/courses.php
<?php
require_once __DIR__ . '/../middleware/auth_check.php';
require_once __DIR__ . '/../middleware/instructor_only.php';
require_once __DIR__ . '/../../includes/db_operations.php';

header('Content-Type: application/json');

class CourseAPI extends DBOperations {
    public function createCourse() {
        instructorOnly();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['code', 'title'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                exit(json_encode(['error' => "$field is required"]));
            }
        }
        
        try {
            $this->executeQuery(
                "INSERT INTO courses (code, title, credits) VALUES (?, ?, ?)",
                [$data['code'], $data['title'], $data['credits'] ?? 3]
            );
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$courseAPI = new CourseAPI();

switch ($method) {
    case 'POST':
        $courseAPI->createCourse();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>