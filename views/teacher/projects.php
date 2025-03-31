<?php
// Get teacher ID from session
$teacherId = $_SESSION['user_id'] ?? 0;

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Process filters
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

// Check for success message in session (from redirect)
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle project status change if requested
if (isset($_GET['change_status']) && isset($_GET['project_id']) && isset($_GET['status'])) {
    $projectId = (int)$_GET['project_id'];
    $newStatus = $_GET['status'];
    
    if ($teacherController->updateProjectStatus($projectId, $newStatus, $teacherId)) {
        $successMessage = "Project status updated successfully.";
    } else {
        $errorMessage = "Failed to update project status.";
    }
}

// Get projects managed by this teacher (using single table approach with filters)
$projects = $teacherController->getTeacherProjectsSingleTable($teacherId, $filters);

// Count projects by status for the summary
$projectCounts = [
    'total' => count($projects),
    'active' => 0,
    'pending' => 0,
    'completed' => 0,
    'archived' => 0
];

foreach ($projects as $project) {
    $status = $project['status'] ?? 'pending';
    $projectCounts[$status]++;
}

// Debug info
$debugInfo = "Teacher ID: " . $teacherId . ", Number of projects: " . count($projects);
?>

<div class="container-fluid px-4">
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Project Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Total Projects</div>
                        <div><h3><?php echo $projectCounts['total']; ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Active</div>
                        <div><h3><?php echo $projectCounts['active']; ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Pending</div>
                        <div><h3><?php echo $projectCounts['pending']; ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Completed</div>
                        <div><h3><?php echo $projectCounts['completed']; ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <i class="fas fa-filter me-1"></i> Filter Projects
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="teacher_projects">
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="archived" <?php echo ($filters['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Project name or description" value="<?php echo $filters['search'] ?? ''; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?php echo $filters['date_from'] ?? ''; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?php echo $filters['date_to'] ?? ''; ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="index.php?page=teacher_projects" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Project List -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-project-diagram me-1"></i> My Projects
            </div>
            <a href="index.php?page=create_project" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Project
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($projects)): ?>
                <div class="alert alert-info">
                    <p>No projects found matching your criteria. <?php if (empty($filters)): ?>Click the "Create New Project" button to get started.<?php endif; ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="projectsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Students</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Timeline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo $project['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($project['name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $description = $project['description'] ?? '';
                                        echo nl2br(htmlspecialchars(substr($description, 0, 100))); 
                                        if (strlen($description) > 100) echo '...';
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $project['student_count']; ?> student(s)</span>
                                        
                                        <?php if (!empty($project['students'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="<?php 
                                                    $names = array_map(function($student) {
                                                        return htmlspecialchars($student['name']);
                                                    }, $project['students']);
                                                    echo implode(', ', $names);
                                                ?>"
                                            >
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($project['teacher_department'] ?? 'Not specified'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusClass = '';
                                            $status = $project['status'] ?? 'pending';
                                            switch ($status) {
                                                case 'active':
                                                    $statusClass = 'success';
                                                    break;
                                                case 'pending':
                                                    $statusClass = 'warning';
                                                    break;
                                                case 'completed':
                                                    $statusClass = 'info';
                                                    break;
                                                case 'archived':
                                                    $statusClass = 'secondary';
                                                    break;
                                            }
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Start:</strong> <?php echo date('M d, Y', strtotime($project['start_date'] ?? 'now')); ?><br>
                                            <strong>End:</strong> <?php echo date('M d, Y', strtotime($project['end_date'] ?? 'now')); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="index.php?page=view_project&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=edit_project&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                More
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="index.php?page=manage_project_students&id=<?php echo $project['id']; ?>">
                                                        <i class="fas fa-users"></i> Manage Students
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="index.php?page=project_diary_entries&id=<?php echo $project['id']; ?>">
                                                        <i class="fas fa-book"></i> View Diary Entries
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item" href="index.php?page=teacher_projects&change_status=1&project_id=<?php echo $project['id']; ?>&status=active">
                                                        <i class="fas fa-check"></i> Mark as Active
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="index.php?page=teacher_projects&change_status=1&project_id=<?php echo $project['id']; ?>&status=completed">
                                                        <i class="fas fa-check-double"></i> Mark as Completed
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="index.php?page=teacher_projects&change_status=1&project_id=<?php echo $project['id']; ?>&status=archived">
                                                        <i class="fas fa-archive"></i> Archive
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
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
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize DataTable
    if (document.getElementById('projectsTable')) {
        $('#projectsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']] // Sort by ID descending
        });
    }
});
</script>