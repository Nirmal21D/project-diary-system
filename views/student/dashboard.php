<?php
// Get student ID from session
$studentId = $_SESSION['user_id'] ?? 0;
$studentName = $_SESSION['name'] ?? 'Student';

// Get dashboard data from controller
try {
    $dashboardData = $studentController->getDashboardData($studentId);
    
    // Extract data with proper fallbacks
    $projects = $dashboardData['projects'] ?? [];
    $recentEntries = $dashboardData['recentEntries'] ?? [];
    $pendingReviews = $dashboardData['pendingReviews'] ?? [];
    $totalEntries = $dashboardData['totalEntries'] ?? 0;
    $reviewedEntries = $dashboardData['reviewedEntries'] ?? 0;

    // Calculate statistics
    $totalProjects = count($projects);
    $activeProjects = 0;
    $completedProjects = 0;
    $pendingProjects = 0;

    // Count project statuses
    foreach ($projects as $project) {
        if ($project['status'] === 'active') {
            $activeProjects++;
        } elseif ($project['status'] === 'completed') {
            $completedProjects++;
        } elseif ($project['status'] === 'pending') {
            $pendingProjects++;
        }
    }

    // Calculate overall progress percentage
    $progressPercentage = ($totalEntries > 0) ? 
        floor(($reviewedEntries / $totalEntries) * 100) : 0;
        
} catch (Exception $e) {
    // Log error and set empty defaults
    error_log("Error loading student dashboard: " . $e->getMessage());
    $projects = [];
    $recentEntries = [];
    $pendingReviews = [];
    $totalEntries = 0;
    $reviewedEntries = 0;
    $totalProjects = 0;
    $activeProjects = 0;
    $completedProjects = 0;
    $pendingProjects = 0;
    $progressPercentage = 0;
    $errorMessage = "Could not load dashboard data. Please try again later.";
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Student Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Projects Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Projects</div>
                        <div><h2><?php echo $totalProjects; ?></h2></div>
                    </div>
                    <div>
                        <span class="badge bg-success"><?php echo $activeProjects; ?> Active</span>
                        <span class="badge bg-secondary"><?php echo $completedProjects; ?> Completed</span>
                        <span class="badge bg-warning"><?php echo $pendingProjects; ?> Pending</span>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=student_projects">View Projects</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <!-- Diary Entries Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Diary Entries</div>
                        <div><h2><?php echo $totalEntries; ?></h2></div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-light" role="progressbar" style="width: <?php echo $progressPercentage; ?>%" 
                             aria-valuenow="<?php echo $progressPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                             <?php echo $progressPercentage; ?>% Reviewed
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=diary_entries">View Entries</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <!-- Pending Reviews Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Pending Reviews</div>
                        <div><h2><?php echo count($pendingReviews); ?></h2></div>
                    </div>
                    <div>Awaiting teacher feedback</div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=pending_reviews">View Pending</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <!-- Add Diary Entry Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Quick Actions</div>
                        <div><i class="fas fa-edit fa-2x"></i></div>
                    </div>
                    <div>Create new diary entry</div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=create_diary_entry">Add Entry</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities Section -->
    <div class="row">
        <!-- Projects List -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-project-diagram me-1"></i> My Projects</div>
                    <a href="index.php?page=student_projects" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($projects)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-1"></i> You don't have any projects yet.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach (array_slice($projects, 0, 5) as $project): ?>
                                <a href="index.php?page=view_project&id=<?php echo $project['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between">
                                    <div>
                                        <span class="fw-bold"><?php echo htmlspecialchars($project['name']); ?></span>
                                        <span class="d-block small text-muted">Teacher: <?php echo htmlspecialchars($project['teacher_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div>
                                        <span class="badge <?php 
                                            echo $project['status'] === 'active' ? 'bg-success' : 
                                                ($project['status'] === 'completed' ? 'bg-secondary' : 'bg-warning'); 
                                        ?>"><?php echo ucfirst($project['status']); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Diary Entries -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-book me-1"></i> Recent Diary Entries</div>
                    <a href="index.php?page=diary_entries" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentEntries)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-1"></i> You haven't created any diary entries yet.
                            <a href="index.php?page=create_diary_entry" class="alert-link">Create your first entry</a>.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recentEntries as $entry): ?>
                                <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($entry['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($entry['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 text-truncate"><?php echo htmlspecialchars(substr($entry['content'], 0, 100)); ?></p>
                                    <small class="text-muted">
                                        Project: <?php echo htmlspecialchars($entry['project_name'] ?? 'Unknown'); ?>
                                        <span class="badge <?php echo $entry['reviewed'] ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $entry['reviewed'] ? 'Reviewed' : 'Pending'; ?>
                                        </span>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>