<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/csrf.php';

header('Content-Type: application/json');

// Auth check - professors/admins only
$currentUser = authenticate();
if (!in_array($currentUser['role'], ['professor', 'admin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Course management access denied']));
}

verifyCSRFToken();

try {
    $courseId = $_GET['course_id'] ?? null;

    // Add prerequisite
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $requiredCourseId = $data['required_course_id'] ?? null;

        if (!$courseId || !$requiredCourseId) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing course IDs']));
        }

        // Prevent self-prerequisite
        if ($courseId == $requiredCourseId) {
            http_response_code(409);
            die(json_encode(['error' => 'Course cannot require itself']));
        }

        // Check if required course exists
        $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ?");
        $stmt->execute([$requiredCourseId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            die(json_encode(['error' => 'Required course not found']));
        }

        // Check for circular dependencies
        if (hasCircularDependency($courseId, $requiredCourseId)) {
            http_response_code(409);
            die(json_encode(['error' => 'Circular dependency detected']));
        }

        $stmt = $conn->prepare("INSERT INTO prerequisites (course_id, required_course_id) VALUES (?, ?)");
        $stmt->execute([$courseId, $requiredCourseId]);
        
        echo json_encode(['message' => 'Prerequisite added']);
    }

    // Remove prerequisite
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $requiredCourseId = $_GET['required_id'] ?? null;
        
        if (!$courseId || !$requiredCourseId) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing course IDs']));
        }

        $stmt = $conn->prepare("DELETE FROM prerequisites WHERE course_id = ? AND required_course_id = ?");
        $stmt->execute([$courseId, $requiredCourseId]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            die(json_encode(['error' => 'Prerequisite not found']));
        }

        echo json_encode(['message' => 'Prerequisite removed']);
    }

    // List prerequisites
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("
            SELECT c.id, c.code, c.title 
            FROM prerequisites p
            JOIN courses c ON p.required_course_id = c.id
            WHERE p.course_id = ?
        ");
        $stmt->execute([$courseId]);
        
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    else {
        http_response_code(405);
        die(json_encode(['error' => 'Method not allowed']));
    }

} catch (PDOException $e) {
    error_log("Prerequisite error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database operation failed']));
}

// Recursive circular dependency check
function hasCircularDependency($targetCourseId, $checkCourseId, $visited = []) {
    global $conn;
    
    if ($targetCourseId == $checkCourseId) return true;
    if (in_array($checkCourseId, $visited)) return false;
    
    $visited[] = $checkCourseId;
    
    $stmt = $conn->prepare("
        SELECT required_course_id 
        FROM prerequisites 
        WHERE course_id = ?
    ");
    $stmt->execute([$checkCourseId]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (hasCircularDependency($targetCourseId, $row['required_course_id'], $visited)) {
            return true;
        }
    }
    
    return false;
}
?>