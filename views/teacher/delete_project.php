<?php
// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$confirm = isset($_GET['confirm']) && $_GET['confirm'] == 1;
$teacherId = $_SESSION['user_id'] ?? 0;

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Security check - make sure the project belongs to the logged-in teacher
if (!$teacherController->isProjectOwnedByTeacher($projectId, $teacherId)) {
    die('Unauthorized access.');
}

// If confirmation is provided, delete the project
if ($confirm) {
    if ($teacherController->deleteProject($projectId)) {
        header('Location: /teacher/projects.php?message=Project+deleted+successfully');
        exit;
    } else {
        die('Failed to delete the project.');
    }
}

// If no confirmation, show a confirmation message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Project</title>
</head>
<body>
    <h1>Delete Project</h1>
    <p>Are you sure you want to delete this project?</p>
    <a href="?id=<?php echo $projectId; ?>&confirm=1">Yes, delete it</a>
    <a href="/teacher/projects.php">No, go back</a>
</body>
</html>