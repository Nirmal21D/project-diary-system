<?php
// Get teacher ID from session
$teacherId = $_SESSION['user_id'] ?? 0;

// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Project not found handling
$redirectToProjects = false;
if (!$projectId) {
    $_SESSION['error_message'] = "No project selected.";
    $redirectToProjects = true;
}

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get project details
if (!$redirectToProjects) {
    $project = $teacherController->getProjectById($projectId);

    // Verify project exists and belongs to this teacher
    if (!$project || $project['teacher_id'] != $teacherId) {
        $_SESSION['error_message'] = "Project not found or you don't have permission to edit it.";
        $redirectToProjects = true;
    }
}

// Handle redirect using JavaScript instead of header()
if ($redirectToProjects) {
?>
    <script>
        window.location.href = 'index.php?page=teacher_projects';
    </script>
<?php
    exit;
}

// Get all available students
$allStudents = $teacherController->getAllStudents();

// Get currently assigned students
$assignedStudentIds = $project['student_ids_array'] ?? [];

$successMessage = '';
$errorMessage = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectData = [
        'id' => $projectId,
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
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
    
    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
    } else {
        // Update project and assigned students
        $result = $teacherController->updateProject($projectData, $selectedStudents);
        
        if ($result === true) {
            $_SESSION['success_message'] = "Project updated successfully!";
?>
            <script>
                window.location.href = 'index.php?page=teacher_projects';
            </script>
<?php
            exit;
        } else {
            // Error case: display the actual error message
            $errorMessage = "Failed to update project: " . $result;
        }
    }
}

// Check for messages in session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message']) && empty($errorMessage)) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
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

    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=teacher_projects">Projects</a></li>
            <li class="breadcrumb-item active">Edit Project</li>
        </ol>
    </nav>
    
    <form method="post" action="">
        <div class="row">
            <!-- Project Details Card -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-edit me-1"></i> Edit Project Details
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Project Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="<?php echo htmlspecialchars($project['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Project Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                required><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php
                                $statuses = ['pending', 'active', 'completed', 'archived'];
                                $currentStatus = $project['status'] ?? 'pending';
                                foreach ($statuses as $status) {
                                    $selected = ($status == $currentStatus) ? 'selected' : '';
                                    echo "<option value=\"{$status}\" {$selected}>" . ucfirst($status) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student Selection Card -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-users me-1"></i> Manage Students
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchStudents" placeholder="Search students...">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label d-block">Assigned Students</label>
                            <div class="border p-3 mb-2" style="height: 300px; overflow-y: auto;">
                                <?php if (empty($allStudents)): ?>
                                    <p class="text-center text-muted">No students available</p>
                                <?php else: ?>
                                    <?php foreach ($allStudents as $student): ?>
                                        <div class="form-check student-item">
                                            <input class="form-check-input" type="checkbox" name="students[]" 
                                                value="<?php echo $student['id']; ?>" id="student<?php echo $student['id']; ?>"
                                                <?php echo in_array($student['id'], $assignedStudentIds) ? 'checked' : ''; ?>>
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
                            <i class="fas fa-info-circle"></i> Adding/removing students will update project assignments.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Section -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Changes
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