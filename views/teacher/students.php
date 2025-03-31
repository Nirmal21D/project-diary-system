<?php
$teacherId = $_SESSION['user_id'] ?? 0;

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

try {
    // Get all students assigned to teacher's projects
    $students = $teacherController->getMyStudents($teacherId);
    
    // Get projects for filtering
    $myProjects = $teacherController->getMyProjects($teacherId);
    
    // Handle filtering by project
    $projectFilter = isset($_GET['project']) ? $_GET['project'] : 'all';
} catch (Exception $e) {
    $errorMessage = "Error loading students: " . $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">My Students</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Students</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Filter Controls -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i> Filter
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="teacher_students">
                
                <div class="col-md-4">
                    <label for="project" class="form-label">Project Group</label>
                    <select class="form-select" id="project" name="project">
                        <option value="all" <?php echo $projectFilter === 'all' ? 'selected' : ''; ?>>All Projects</option>
                        <?php foreach ($myProjects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" <?php echo $projectFilter == $project['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    <a href="index.php?page=teacher_students" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i> Students
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    No students found. You need to create project groups and assign students to them.
                </div>
                <div class="text-center mt-3">
                    <a href="index.php?page=create_project_group" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Project Group
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="studentsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Projects</th>
                                <th>Latest Activity</th>
                                <th>Total Entries</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <?php 
                                    // Skip if filtering by project and student is not in that project
                                    if ($projectFilter !== 'all') {
                                        $inProject = false;
                                        foreach ($student['projects'] as $project) {
                                            if ($project['id'] == $projectFilter) {
                                                $inProject = true;
                                                break;
                                            }
                                        }
                                        if (!$inProject) continue;
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                                <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($student['name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td>
                                        <?php if (!empty($student['projects'])): ?>
                                            <?php foreach (array_slice($student['projects'], 0, 2) as $index => $project): ?>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($project['name']); ?></span>
                                            <?php endforeach; ?>
                                            
                                            <?php if (count($student['projects']) > 2): ?>
                                                <span class="badge bg-secondary">+<?php echo (count($student['projects']) - 2); ?> more</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No projects</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($student['last_activity'])): ?>
                                            <?php echo date('M j, Y', strtotime($student['last_activity'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No activity</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <span class="badge bg-info"><?php echo $student['total_entries'] ?? 0; ?> Total</span>
                                            </div>
                                            <?php if (isset($student['pending_reviews']) && $student['pending_reviews'] > 0): ?>
                                                <span class="badge bg-warning"><?php echo $student['pending_reviews']; ?> Pending</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="index.php?page=view_student&id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="index.php?page=student_diary_entries&student=<?php echo $student['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-book"></i> Entries
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable if it exists
        if (typeof $.fn.DataTable !== 'undefined' && $('#studentsTable').length > 0) {
            $('#studentsTable').DataTable({
                responsive: true,
                order: [[4, 'desc']], // Sort by total entries column by default
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100]
            });
        }
    });
</script>