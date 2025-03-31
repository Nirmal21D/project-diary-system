<?php
// Get teacher ID and role from session
$teacherId = $_SESSION['user_id'] ?? 0;
$teacherRole = $_SESSION['role'] ?? '';

// Verify that user is a teacher
if ($teacherRole !== 'teacher') {
    $errorMessage = "Your account doesn't have teacher privileges. Please contact the administrator.";
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get teacher details
$teacher = $teacherController->getTeacherDetails($teacherId);

// Verify teacher exists in database
if (!$teacher) {
    $errorMessage = "Your teacher profile could not be found. Please contact the administrator.";
}

// Get all available students
$allStudents = $teacherController->getAllStudents();

// Create test student if no students exist
if (empty($allStudents)) {
    $teacherController->ensureTestStudentExists();
    // Refresh the student list
    $allStudents = $teacherController->getAllStudents();
}

$successMessage = '';
$errorMessage = '';
$debugMessage = ''; // Add debug message variable
$projectData = [
    'name' => '',
    'description' => '',
    'status' => 'active',
    'teacher_id' => $teacherId
];
$selectedStudents = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectData = [
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'teacher_id' => $teacherId
    ];
    
    // Get selected students
    $selectedStudents = isset($_POST['students']) ? $_POST['students'] : [];
    
    // Validation
    $errors = [];
    
    if (empty($projectData['name'])) {
        $errors[] = 'Project name is required.';
    }
    
    if (empty($projectData['description'])) {
        $errors[] = 'Project description is required.';
    }
    
    if (empty($selectedStudents)) {
        $errors[] = 'Please select at least one student for the project.';
    }
    
    // Verify teacher role again
    if ($teacherRole !== 'teacher' || !$teacher) {
        $errors[] = 'You must be a teacher to create projects.';
    }
    
    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
    } else {
        // Create project with students using single table approach
        $result = $teacherController->createProjectWithStudentsSingleTable($projectData, $selectedStudents);
        
        if ($result === true) {
            // Store success message and redirect to projects page
            $_SESSION['success_message'] = "Project created successfully with " . count($selectedStudents) . " students assigned!";
            
            echo "<script>window.location.href = 'index.php?page=teacher_projects';</script>";
            exit;
        } else {
            // Error case: display the actual error message
            $errorMessage = "Failed to create project: " . $result;
        }
    }
}
// Check for success message in session (from redirect)
else if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<div class="container-fluid">
    <h1 class="mt-4">Create New Project</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=projects">Projects</a></li>
        <li class="breadcrumb-item active">Create Project</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-project-diagram me-1"></i> Project Details
        </div>
        <div class="card-body">
            <form method="post" action="index.php?page=create_project">
                <div class="mb-3">
                    <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" selected>Active</option>
                        <option value="planning">Planning</option>
                        <option value="completed">Completed</option>
                        <option value="on_hold">On Hold</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="students" class="form-label">Select Students <span class="text-danger">*</span></label>
                    <select class="form-select" id="students" name="students[]" multiple required>
                        <?php foreach ($allStudents as $student): ?>
                            <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold Ctrl (or Cmd on Mac) to select multiple students.</div>
                </div>
                
                <button type="submit" class="btn btn-primary">Create Project</button>
                <a href="index.php?page=projects" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>