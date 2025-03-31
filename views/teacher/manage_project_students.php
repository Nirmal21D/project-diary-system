<?php
// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacherId = $_SESSION['user_id'] ?? 0;

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get project details
$project = $teacherController->getProjectDetails($projectId, $teacherId);

// Check if project exists and belongs to this teacher
if (!$project) {
    $errorMessage = "Project not found or you don't have permission to manage it.";
}

// Get all students
$allStudents = $teacherController->getAllStudents();

// Get currently assigned students
$currentStudentIds = [];
if ($project) {
    $currentStudentIds = json_decode($project['student_ids'] ?? '[]', true);
}

$successMessage = '';

// Process student update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $project) {
    // Get selected students
    $selectedStudents = isset($_POST['students']) ? $_POST['students'] : [];
    
    // Update project students
    $result = $teacherController->updateProjectStudents($projectId, $selectedStudents, $teacherId);
    
    if ($result) {
        $successMessage = "Students updated successfully!";
        
        // Refresh project data and currently assigned students
        $project = $teacherController->getProjectDetails($projectId, $teacherId);
        $currentStudentIds = json_decode($project['student_ids'] ?? '[]', true);
    } else {
        $errorMessage = "Failed to update students. Please try again.";
    }
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
    
    <?php if ($project): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-users-cog me-1"></i> Manage Students - 
                    <?php echo htmlspecialchars($project['name']); ?>
                </div>
                <div>
                    <a href="index.php?page=view_project&id=<?php echo $projectId; ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-eye"></i> View Project
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchStudents" placeholder="Search students...">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" id="selectAll">Select All</button>
                                <button type="button" class="btn btn-outline-secondary" id="deselectAll">Deselect All</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border rounded p-3 mb-3" style="height: 400px; overflow-y: auto;">
                        <?php if (empty($allStudents)): ?>
                            <div class="alert alert-info">
                                No students available. Please create student accounts first.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($allStudents as $student): ?>
                                    <div class="col-lg-6 mb-3 student-item">
                                        <div class="card h-100">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="students[]" 
                                                        value="<?php echo $student['id']; ?>" id="student<?php echo $student['id']; ?>"
                                                        <?php echo in_array($student['id'], $currentStudentIds) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label w-100" for="student<?php echo $student['id']; ?>">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div class="avatar-circle bg-primary text-white">
                                                                    <?php echo substr($student['name'], 0, 1); ?>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h6 class="mb-0"><?php echo htmlspecialchars($student['name']); ?></h6>
                                                                <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="index.php?page=view_project&id=<?php echo $projectId; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="card mb-4">
            <div class="card-body text-center py-5">
                <h4 class="text-muted mb-4">Project not found</h4>
                <a href="index.php?page=teacher_projects" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
}
</style>

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