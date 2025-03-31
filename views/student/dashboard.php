<?php
// Extract data at the top of the file
$projects = $dashboardData['projects'] ?? [];
$recentEntries = $dashboardData['recentEntries'] ?? [];
$pendingReviews = $dashboardData['pendingReviews'] ?? [];
$totalEntries = $dashboardData['totalEntries'] ?? 0;
$reviewedEntries = $dashboardData['reviewedEntries'] ?? 0;

// Debug info (remove in production)
echo "<!-- Projects: " . count($projects) . " -->";
echo "<!-- Recent Entries: " . count($recentEntries) . " -->";
echo "<!-- Pending Reviews: " . count($pendingReviews) . " -->";

// Get student data via controller
$studentId = $_SESSION['user_id'] ?? 0;
$studentName = $_SESSION['name'] ?? 'Student';

// Calculate statistics
$totalProjects = count($projects);
$activeProjects = 0;

// Count active projects
foreach ($projects as $project) {
    if ($project['status'] === 'active') {
        $activeProjects++;
    }
}

// Calculate overall progress
$overallProgress = $totalProjects ? round(($reviewedEntries / ($totalEntries ?: 1)) * 100) : 0;
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Projects</div>
                            <div class="display-6"><?php echo count($projects); ?></div>
                        </div>
                        <i class="fas fa-project-diagram fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=projects">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Entries</div>
                            <div class="display-6"><?php echo $totalEntries; ?></div>
                        </div>
                        <i class="fas fa-book fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=diary_entries">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Pending Reviews</div>
                            <div class="display-6"><?php echo count($pendingReviews); ?></div>
                        </div>
                        <i class="fas fa-clock fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=diary_entries#pending">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Reviewed Entries</div>
                            <div class="display-6"><?php echo $reviewedEntries; ?></div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=diary_entries#reviewed">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Entries -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Recent Diary Entries
        </div>
        <div class="card-body">
            <?php if (empty($recentEntries)): ?>
                <p class="text-center">You haven't created any diary entries yet.</p>
                <div class="text-center mt-3">
                    <a href="index.php?page=create_diary_entry" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Your First Entry
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEntries as $entry): ?>
                            <tr>
                                <td><?php echo isset($entry['entry_date']) ? date('M d, Y', strtotime($entry['entry_date'])) : date('M d, Y', strtotime($entry['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                <td><?php echo htmlspecialchars($entry['project_name'] ?? 'Unknown Project'); ?></td>
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
                <div class="text-end mt-3">
                    <a href="index.php?page=diary_entries" class="btn btn-primary me-2">View All Entries</a>
                    <a href="index.php?page=create_diary_entry" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> New Entry
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- My Projects -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-project-diagram me-1"></i> My Projects
        </div>
        <div class="card-body">
            <?php if (empty($projects)): ?>
                <p class="text-center">You are not assigned to any projects yet.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($projects as $project): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($project['name'] ?? 'Unknown Project'); ?></h5>
                                <span class="badge bg-<?php echo ($project['status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($project['status'] ?? 'unknown')); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo htmlspecialchars(substr($project['description'] ?? 'No description available', 0, 100) . '...'); ?></p>
                                <p class="card-text"><small class="text-muted">Teacher: <?php echo htmlspecialchars($project['teacher_name'] ?? 'Unknown'); ?></small></p>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="index.php?page=view_project&id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                <a href="index.php?page=create_diary_entry&project_id=<?php echo $project['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-1"></i> New Entry
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-end mt-3">
                    <a href="index.php?page=projects" class="btn btn-primary">View All Projects</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>