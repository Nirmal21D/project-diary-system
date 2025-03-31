<?php
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/ProjectGroup.php'; 
require_once BASE_PATH . '/models/DiaryEntry.php';
require_once BASE_PATH . '/models/Notification.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../login.php");
    exit();
}

// Get teacher ID from session
$teacherId = $_SESSION['user_id'] ?? 0;

// Get teacher name with fallback
$teacherName = $_SESSION['name'] ?? 'Teacher';

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Initialize arrays to avoid undefined variable errors
$projects = [];
$recentEntries = [];
$pendingReviews = [];
$notifications = [];

// Only fetch data if we have a valid teacher ID
if ($teacherId > 0) {
    // Get teacher data
    $teacher = $teacherController->getTeacherDetails($teacherId);
    
    // Get projects managed by this teacher
    $projects = $teacherController->getTeacherProjects($teacherId);
    
    // Get recent diary entries
    $recentEntries = $teacherController->getRecentDiaryEntries($teacherId);
    
    // Get pending reviews
    $pendingReviews = $teacherController->getPendingReviews($teacherId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
 
    

    <div class="container-fluid px-4">
        <!-- Welcome Message -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Welcome, <?php echo htmlspecialchars($teacherName); ?>!</h2>
                        <p class="card-text">This is your teacher dashboard for Project Diary System. Here you can manage your projects, review student diary entries, and provide feedback.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-6 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="display-4 mb-0"><?php echo count($projects); ?></h2>
                                <h4>My Projects</h4>
                                <p class="mb-0">
                                    <?php 
                                    $activeCount = 0;
                                    $pendingCount = 0;
                                    foreach ($projects as $p) {
                                        if ($p['status'] === 'active') $activeCount++;
                                        if ($p['status'] === 'pending') $pendingCount++;
                                    }
                                    echo "$activeCount active, $pendingCount pending";
                                    ?>
                                </p>
                            </div>
                            <div class="text-white">
                                <i class="fas fa-project-diagram fa-5x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="text-white stretched-link" href="index.php?page=teacher_projects">View Projects</a>
                        <div class="text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>Students</div>
                            <div><h2><?php echo $teacherId > 0 ? $teacherController->getStudentCount($teacherId) : 0; ?></h2></div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="index.php?page=teacher_students">View Students</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>Diary Entries</div>
                            <div><h2><?php echo $teacherId > 0 ? $teacherController->getDiaryEntryCount($teacherId) : 0; ?></h2></div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="index.php?page=teacher_diary_entries">View Entries</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>Pending Reviews</div>
                            <div><h2><?php echo count($pendingReviews); ?></h2></div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="index.php?page=teacher_pending_reviews">View Pending</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Recent Diary Entries -->
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-book me-1"></i> Recent Diary Entries
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentEntries)): ?>
                            <p class="text-center">No recent diary entries.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Project</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEntries as $entry): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($entry['project_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($entry['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($entry['reviewed']): ?>
                                                        <span class="badge bg-success">Reviewed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="index.php?page=teacher_diary_entries" class="btn btn-sm btn-primary">View All Entries</a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-tasks me-1"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php?page=create_project" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Create New Project
                            </a>
                            <a href="index.php?page=teacher_pending_reviews" class="btn btn-warning">
                                <i class="fas fa-clipboard-check"></i> Review Pending Entries
                            </a>
                            <a href="index.php?page=teacher_schedule" class="btn btn-info">
                                <i class="fas fa-calendar-alt"></i> View Schedule
                            </a>
                            <a href="index.php?page=teacher_reports" class="btn btn-success">
                                <i class="fas fa-chart-bar"></i> Generate Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>
</body>
</html>