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
    'start_date' => date('Y-m-d'),
    'end_date' => date('Y-m-d', strtotime('+1 month')),
    'status' => 'pending',
    'teacher_id' => $teacherId
];
$selectedStudents = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectData = [
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'status' => $_POST['status'] ?? 'pending',
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
    
    if (empty($projectData['start_date'])) {
        $errors[] = 'Start date is required.';
    }
    
    if (empty($projectData['end_date'])) {
        $errors[] = 'End date is required.';
    } else if ($projectData['end_date'] < $projectData['start_date']) {
        $errors[] = 'End date cannot be before start date.';
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

<div class="container-fluid px-4">
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($debugMessage)): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <h5>Debug Information</h5>
            <pre><?php echo htmlspecialchars($debugMessage); ?></pre>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Debug Panel -->
    <div class="card mb-4 bg-light">
        <div class="card-header bg-info text-white">
            <i class="fas fa-bug"></i> Debug Info (Remove in Production)
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Session Information:</strong></p>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            User ID: 
                            <span class="badge bg-primary"><?php echo $teacherId; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Role: 
                            <span class="badge bg-<?php echo $teacherRole === 'teacher' ? 'success' : 'danger'; ?>">
                                <?php echo $teacherRole ?: 'Not Set'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Teacher Profile: 
                            <span class="badge bg-<?php echo $teacher ? 'success' : 'danger'; ?>">
                                <?php echo $teacher ? 'Found' : 'Not Found'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Available Students: 
                            <span class="badge bg-info"><?php echo count($allStudents); ?></span>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <p><strong>Database Tables:</strong></p>
                    <ul class="list-group">
                        <?php
                        $tables = ['users', 'projects', 'diary_entries', 'notifications'];
                        foreach ($tables as $table) {
                            $exists = $teacherController->checkTableExists($table);
                            echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                ' . $table . ': 
                                <span class="badge bg-' . ($exists ? 'success' : 'danger') . '">
                                    ' . ($exists ? 'Exists' : 'Missing') . '
                                </span>
                            </li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            
            <?php if ($teacher): ?>
            <div class="mt-3">
                <p><strong>Teacher Information:</strong></p>
                <pre class="bg-dark text-light p-2"><?php print_r($teacher); ?></pre>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <form action="index.php?page=fix_teacher_role" method="post" class="d-inline">
                    <input type="hidden" name="user_id" value="<?php echo $teacherId; ?>">
                    <button type="submit" class="btn btn-warning">Fix Teacher Role</button>
                </form>
                
                <form action="index.php?page=create_tables" method="post" class="d-inline">
                    <button type="submit" class="btn btn-primary">Create Missing Tables</button>
                </form>
            </div>
        </div>
    </div>
    
    <form method="post" action="">
        <div class="row">
            <!-- Project Details Card -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-project-diagram me-1"></i> Project Details
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Project Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="<?php echo htmlspecialchars($projectData['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Project Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                required><?php echo htmlspecialchars($projectData['description']); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                    value="<?php echo $projectData['start_date']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                    value="<?php echo $projectData['end_date']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Initial Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?php echo $projectData['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="active" <?php echo $projectData['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo $projectData['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student Selection Card -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-users me-1"></i> Assign Students
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchStudents" placeholder="Search students...">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label d-block">Select Students *</label>
                            <div class="border p-3 mb-2" style="height: 300px; overflow-y: auto;">
                                <?php if (empty($allStudents)): ?>
                                    <p class="text-center text-muted">No students available</p>
                                <?php else: ?>
                                    <?php foreach ($allStudents as $student): ?>
                                        <div class="form-check student-item">
                                            <input class="form-check-input" type="checkbox" name="students[]" 
                                                value="<?php echo $student['id']; ?>" id="student<?php echo $student['id']; ?>"
                                                <?php echo isset($selectedStudents) && in_array($student['id'], $selectedStudents) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="student<?php echo $student['id']; ?>">
                                                <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Deselect All</button>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i> Selected students will be added to this project group.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Section -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Create Project with Students
                        </button>
                        <a href="index.php?page=teacher_projects" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchBox = document.getElementById('searchStudents');
    const studentItems = document.querySelectorAll('.student-item');
    const clearSearch = document.getElementById('clearSearch');
    
    searchBox.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        
        studentItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    clearSearch.addEventListener('click', function() {
        searchBox.value = '';
        studentItems.forEach(item => {
            item.style.display = 'block';
        });
    });
    
    // Select/Deselect all
    document.getElementById('selectAll').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="students[]"]');
        checkboxes.forEach(checkbox => {
            const item = checkbox.closest('.student-item');
            if (item.style.display !== 'none') {
                checkbox.checked = true;
            }
        });
    });
    
    document.getElementById('deselectAll').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="students[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    });
});
</script>