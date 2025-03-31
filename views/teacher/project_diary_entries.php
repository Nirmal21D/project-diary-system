<?php
// Get teacher ID from session
$teacherId = $_SESSION['user_id'] ?? 0;

// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$projectId) {
    // If no project ID provided, redirect to projects list
    $_SESSION['error_message'] = "No project selected.";
    header("Location: index.php?page=teacher_projects");
    exit;
}

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get project details
$project = $teacherController->getProjectDetails($projectId, $teacherId);

if (!$project) {
    // If project not found or doesn't belong to this teacher
    $_SESSION['error_message'] = "Project not found or you don't have permission to view it.";
    header("Location: index.php?page=teacher_projects");
    exit;
}

// Get filter parameters
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get diary entries for this project
$diaryEntries = $teacherController->getProjectDiaryEntries($projectId, $studentId, $status);

// Get students in this project for filter
$students = [];
$studentIds = json_decode($project['student_ids'] ?? '[]', true);
if (!empty($studentIds)) {
    $students = $teacherController->getStudentsInfo($studentIds);
}
?>

<div class="container-fluid px-4">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=teacher_projects">Projects</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=view_project&id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></a></li>
            <li class="breadcrumb-item active">Diary Entries</li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-book me-1"></i> Diary Entries: <?php echo htmlspecialchars($project['name']); ?>
            </div>
            <div>
                <a href="index.php?page=view_project&id=<?php echo $project['id']; ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-eye"></i> View Project
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="get" action="index.php" class="row g-3">
                        <input type="hidden" name="page" value="project_diary_entries">
                        <input type="hidden" name="id" value="<?php echo $projectId; ?>">
                        
                        <div class="col-md-4">
                            <label for="student_id" class="form-label">Filter by Student</label>
                            <select class="form-select" id="student_id" name="student_id">
                                <option value="">All Students</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo $studentId == $student['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="status" class="form-label">Filter by Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="index.php?page=project_diary_entries&id=<?php echo $projectId; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sync"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Diary Entries Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($diaryEntries)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No diary entries found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($diaryEntries as $entry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($entry['created_at'])); ?></td>
                                    <td>
                                        <?php if ($entry['reviewed']): ?>
                                            <span class="badge bg-success">Reviewed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>&project_id=<?php echo $projectId; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>