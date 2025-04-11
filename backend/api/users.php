<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';

header('Content-Type: application/json');

// Authentication check
$currentUser = authenticate();
if ($currentUser['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

// GET: List users with search
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    try {
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt = $conn->prepare("
                SELECT id, first_name, last_name, email, role, is_active
                FROM users 
                WHERE (first_name LIKE :search OR email LIKE :search)
                AND is_active = 1
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("
                SELECT id, first_name, last_name, email, role, is_active
                FROM users 
                WHERE is_active = 1
                LIMIT :limit OFFSET :offset
            ");
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        // Get total count for pagination
        $countStmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM users 
            WHERE is_active = 1" . 
            (!empty($search) ? " AND (first_name LIKE :search OR email LIKE :search)" : "")
        );
        if (!empty($search)) {
            $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        echo json_encode([
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// POST: Create new user
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validation
    $required = ['first_name', 'last_name', 'email', 'role'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "$field is required"]);
            exit;
        }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    try {
        // Generate temporary password
        $tempPassword = bin2hex(random_bytes(4));
        $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            INSERT INTO users 
            (first_name, last_name, email, role, password) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            htmlspecialchars($data['first_name']),
            htmlspecialchars($data['last_name']),
            $data['email'],
            $data['role'],
            $hashedPassword
        ]);

        // In production: Send email with $tempPassword
        error_log("New user created. Temp password: $tempPassword");

        http_response_code(201);
        echo json_encode([
            'message' => 'User created',
            'id' => $conn->lastInsertId()
        ]);

    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) { // Duplicate email
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
    }
}

// PUT: Update user
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents('php://input'), $data);
    $userId = $_GET['id'] ?? null;

    if (!$userId || !is_numeric($userId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user ID']);
        exit;
    }

    $allowedFields = ['first_name', 'last_name', 'email', 'role', 'is_active'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $field === 'is_active' ? 
                intval($data[$field]) : 
                htmlspecialchars($data[$field]);
        }
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields to update']);
        exit;
    }

    $params[] = $userId;

    try {
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        echo json_encode(['message' => 'User updated']);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Update failed: ' . $e->getMessage()]);
    }
}

// DELETE: Deactivate user (soft delete)
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $userId = $_GET['id'] ?? null;

    if (!$userId || !is_numeric($userId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        } else {
            echo json_encode(['message' => 'User deactivated']);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Deactivation failed']);
    }
}

// Invalid method
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>