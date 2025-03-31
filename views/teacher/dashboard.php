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

// Check if the teacher exists
try {
    // Get dashboard data
    $dashboardData = $teacherController->getDashboardData($teacherId);
    
    // Extract dashboard components
    $projectsCount = $dashboardData['projectsCount'] ?? 0;
    $studentsCount = $dashboardData['studentsCount'] ?? 0;
    $pendingReviewsCount = $dashboardData['pendingReviewsCount'] ?? 0;
    $recentEntries = $dashboardData['recentEntries'] ?? [];
    $projectGroups = $dashboardData['projectGroups'] ?? [];
    
} catch (Exception $e) {
    $errorMessage = "Error loading dashboard: " . $e->getMessage();
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
        <h1 class="mt-4">Teacher Dashboard</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <!-- Dashboard Overview Cards -->
        <div class="row">
            <div class="col-xl-4 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $projectsCount; ?></h4>
                                <div>Active Projects</div>
                            </div>
                            <div>
                                <i class="fas fa-project-diagram fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="index.php?page=teacher_projects">View Projects</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $studentsCount; ?></h4>
                                <div>Students</div>
                            </div>
                            <div>
                                <i class="fas fa-users fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="index.php?page=teacher_students">Manage Students</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $pendingReviewsCount; ?></h4>
                                <div>Pending Reviews</div>
                            </div>
                            <div>
                                <i class="fas fa-clipboard-check fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="index.php?page=student_diary_entries&filter=pending">View Pending Reviews</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Entries Table -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-clock me-1"></i>
                        Recent Diary Entries
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentEntries)): ?>
                            <div class="alert alert-info mb-0">No recent diary entries found.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Student</th>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEntries as $entry): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($entry['entry_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                            <td>
                                                <?php if ($entry['reviewed']): ?>
                                                    <span class="badge bg-success">Reviewed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php?page=student_diary_entries" class="btn btn-outline-primary">View All Entries</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Project Groups -->
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-users me-1"></i>
                        My Project Groups
                    </div>
                    <div class="card-body">
                        <?php if (empty($projectGroups)): ?>
                            <div class="alert alert-info mb-3">
                                You don't have any project groups yet.
                            </div>
                            <a href="index.php?page=create_project_group" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Project Group
                            </a>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($projectGroups as $group): ?>
                                    <a href="index.php?page=view_project_group&id=<?php echo $group['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($group['name']); ?></h5>
                                            <small class="text-<?php echo $group['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($group['status']); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars(substr($group['description'] ?? '', 0, 100)) . (strlen($group['description'] ?? '') > 100 ? '...' : ''); ?></p>
                                        <small>
                                            <?php echo count($group['members'] ?? []); ?> Students
                                            • Created <?php echo date('M j, Y', strtotime($group['created_at'])); ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php?page=teacher_projects" class="btn btn-outline-primary">View All Projects</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>
</body>
</html>