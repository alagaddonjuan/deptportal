// public/course-management/index.php
<?php
require_once __DIR__ . '/../../includes/check_auth.php';
require_once __DIR__ . '/../../includes/auth_functions.php';

if (!userHasRole('instructor')) {
    header('Location: /');
    exit;
}

// Fetch courses for dropdown
global $conn;
$courses = [];
$result = $conn->query("SELECT id, code, title FROM courses");
if ($result) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Management</title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="container">
        <h2>Course Management</h2>
        
        <form id="createCourseForm">
            <div class="form-group">
                <label>Course Code:</label>
                <input type="text" name="code" required>
            </div>
            <div class="form-group">
                <label>Course Title:</label>
                <input type="text" name="title" required>
            </div>
            <button type="submit">Create Course</button>
        </form>
        
        <div id="responseMessage"></div>
        
        <h3>Existing Courses</h3>
        <ul id="courseList">
            <?php foreach ($courses as $course): ?>
                <li><?= htmlspecialchars($course['code']) ?> - <?= htmlspecialchars($course['title']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <script src="/js/course_management.js"></script>
</body>
</html>