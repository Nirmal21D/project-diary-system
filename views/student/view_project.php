<?php
// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get student ID from session
$studentId = $_SESSION['user_id'] ?? 0;

if (!$projectId) {
    $_SESSION['error_message'] = "No project selected.";
    echo "<script>window.location.href = 'index.php?page=student_projects';</script>";
    exit;
}

// Get project details
$project = $studentController->getProjectById($projectId, $studentId);

// Verify project exists and student has access
if (!$project) {
    $_SESSION['error_message'] = "Project not found or you don't have permission to view it.";
    echo "<script>window.location.href = 'index.php?page=student_projects';</script>";
    exit;
}

// Get diary entries for this project
$diaryEntries = $studentController->getProjectDiaryEntries($projectId, $studentId);
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo htmlspecialchars($project['name']); ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=student_projects">My Projects</a></li>
        <li class="breadcrumb-item active">Project Details</li>
    </ol>
    
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
    
    <div class="row">
        <!-- Project Details -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i> Project Details
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Project Name:</div>
                        <div class="col-md-9"><?php echo htmlspecialchars($project['name']); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Description:</div>
                        <div class="col-md-9"><?php echo nl2br(htmlspecialchars($project['description'] ?? '')); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Start Date:</div>
                        <div class="col-md-9"><?php echo date('F j, Y', strtotime($project['start_date'] ?? 'now')); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Status:</div>
                        <div class="col-md-9">
                            <span class="badge bg-<?php 
                                switch ($project['status']) {
                                    case 'active': echo 'success'; break;
                                    case 'pending': echo 'warning'; break;
                                    case 'completed': echo 'info'; break;
                                    default: echo 'secondary';
                                }
                            ?>">
                                <?php echo ucfirst($project['status'] ?? 'unknown'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Teacher:</div>
                        <div class="col-md-9"><?php echo htmlspecialchars($project['teacher_name'] ?? 'Unknown'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Project Actions -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i> Actions
                </div>
                <div class="card-body">
                    <a href="index.php?page=create_diary_entry&project_id=<?php echo $project['id']; ?>" class="btn btn-success btn-lg w-100 mb-3">
                        <i class="fas fa-plus me-2"></i> Create Diary Entry
                    </a>
                    
                    <a href="index.php?page=diary_entries&project_id=<?php echo $project['id']; ?>" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-book me-2"></i> View My Entries
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Diary Entries for this Project -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-book me-1"></i> Project Diary Entries
        </div>
        <div class="card-body">
            <?php if (empty($diaryEntries)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i> You haven't created any diary entries for this project yet.
                </div>
                <div class="text-center mt-3">
                    <a href="index.php?page=create_diary_entry&project_id=<?php echo $project['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Your First Entry
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="entriesTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Created On</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diaryEntries as $entry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($entry['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $entry['reviewed'] ? 'success' : 'warning'; ?>">
                                            <?php echo $entry['reviewed'] ? 'Reviewed' : 'Pending Review'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if (!$entry['reviewed']): ?>
                                            <a href="index.php?page=edit_diary_entry&id=<?php echo $entry['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>
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
    // Initialize DataTable for entries
    const entriesTable = document.getElementById('entriesTable');
    if (entriesTable) {
        new simpleDatatables.DataTable(entriesTable);
    }
});
</script>