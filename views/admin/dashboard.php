<?php
// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Ensure AdminController is available
require_once BASE_PATH . '/controllers/AdminController.php';

// Initialize AdminController with database connection
$adminController = new AdminController($pdo);

// Get statistics
$totalUsers = $adminController->getUserCount() ?: 0;
$adminCount = $adminController->getUserCountByRole('admin') ?: 0;
$teacherCount = $adminController->getUserCountByRole('teacher') ?: 0;
$studentCount = $adminController->getUserCountByRole('student') ?: 0;
$projectCount = $adminController->getProjectCount() ?: 0;

// Get recent data
$recentUsers = $adminController->getRecentUsers(5) ?: [];
$recentProjects = $adminController->getRecentProjects(5) ?: [];

$pageTitle = "Admin Dashboard";
?>

<div class="container-fluid">
    <h1 class="mt-4">Admin Dashboard</h1>
    <p>Welcome to the Project Diary System administration panel.</p>
    
    <!-- Stats Cards Row -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Total Users</div>
                        <div><h2><?php echo $totalUsers; ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="?page=manage_users">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Teachers</div>
                        <div><h2><?php echo $teacherCount; ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="?page=manage_users&role=teacher">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Students</div>
                        <div><h2><?php echo $studentCount; ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="?page=manage_users&role=student">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Projects</div>
                        <div><h2><?php echo $projectCount; ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="?page=manage_projects">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Data Row -->
    <div class="row">
        <!-- Recent Users -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users mr-1"></i>
                    Recently Added Users
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Date Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentUsers)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'teacher' ? 'warning' : 'success'); ?>">
                                                <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="?page=manage_users" class="btn btn-sm btn-primary">View All Users</a>
                </div>
            </div>
        </div>
        
        <!-- Recent Projects -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Recent Projects
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Teacher</th>
                                    <th>Students</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentProjects)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No projects found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentProjects as $project): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($project['name'] ?? 'Untitled'); ?></td>
                                        <td><?php echo htmlspecialchars($project['teacher_name'] ?? 'Unknown'); ?></td>
                                        <td><?php echo $project['student_count'] ?? 0; ?></td>
                                        <td><?php echo isset($project['created_at']) ? date('M d, Y', strtotime($project['created_at'])) : 'Unknown'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="?page=manage_projects" class="btn btn-sm btn-primary">View All Projects</a>
                </div>
            </div>
        </div>
    </div>
</div>