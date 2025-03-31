<?php
// Get teacher ID from session
$teacherId = $_SESSION['user_id'] ?? 0;

// Get entry ID from URL
$entryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

if (!$entryId) {
    // If no entry ID provided, redirect to projects list
    $_SESSION['error_message'] = "No diary entry selected.";
    
    if ($projectId) {
        header("Location: index.php?page=project_diary_entries&id=" . $projectId);
    } else {
        header("Location: index.php?page=teacher_diary_entries");
    }
    exit;
}

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get entry details
$entry = $teacherController->getDiaryEntryDetails($entryId, $teacherId);

if (!$entry) {
    // If entry not found or doesn't belong to this teacher's projects
    $_SESSION['error_message'] = "Diary entry not found or you don't have permission to view it.";
    
    if ($projectId) {
        header("Location: index.php?page=project_diary_entries&id=" . $projectId);
    } else {
        header("Location: index.php?page=teacher_diary_entries");
    }
    exit;
}

// Get project details (may be null if entry has no valid project)
$project = null;
if (!empty($entry['project_id'])) {
    $project = $teacherController->getProjectDetails($entry['project_id'], $teacherId);
}

// Process feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_feedback') {
    $feedback = trim($_POST['feedback'] ?? '');
    
    if (!empty($feedback)) {
        $result = $teacherController->submitFeedback($entryId, $feedback);
        
        if ($result) {
            $_SESSION['success_message'] = "Feedback submitted successfully.";
            // Refresh the entry to show the updated data
            $entry = $teacherController->getDiaryEntryDetails($entryId, $teacherId);
        } else {
            $_SESSION['error_message'] = "Failed to submit feedback. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Please provide feedback before submitting.";
    }
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
            <?php if ($project): ?>
                <li class="breadcrumb-item"><a href="index.php?page=view_project&id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></a></li>
                <li class="breadcrumb-item"><a href="index.php?page=project_diary_entries&id=<?php echo $project['id']; ?>">Diary Entries</a></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="index.php?page=teacher_diary_entries">Diary Entries</a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active">View Entry</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Diary Entry Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <div>
                        <i class="fas fa-book me-1"></i> Diary Entry Details
                    </div>
                    <div>
                        <span class="badge bg-<?php echo $entry['reviewed'] ? 'success' : 'warning'; ?>">
                            <?php echo $entry['reviewed'] ? 'Reviewed' : 'Pending Review'; ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <h4 class="card-title"><?php echo htmlspecialchars($entry['title']); ?></h4>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <?php echo date('F d, Y h:i A', strtotime($entry['created_at'])); ?>
                        </small>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <?php echo nl2br(htmlspecialchars($entry['content'])); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($entry['attachments'])): ?>
                        <div class="mb-4">
                            <h5>Attachments</h5>
                            <div class="list-group">
                                <?php 
                                $attachments = json_decode($entry['attachments'], true);
                                foreach ($attachments as $attachment): 
                                ?>
                                    <a href="<?php echo htmlspecialchars($attachment['path']); ?>" class="list-group-item list-group-item-action" target="_blank">
                                        <i class="fas fa-file me-2"></i> <?php echo htmlspecialchars($attachment['name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Feedback Section -->
                    <div class="mt-4">
                        <h5>
                            Feedback 
                            <?php if ($entry['reviewed']): ?>
                                <span class="badge bg-success">Provided</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Required</span>
                            <?php endif; ?>
                        </h5>
                        
                        <?php if ($entry['reviewed'] && !empty($entry['feedback'])): ?>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Your feedback:</h6>
                                    <?php echo nl2br(htmlspecialchars($entry['feedback'])); ?>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary mt-3" id="editFeedbackBtn">
                                <i class="fas fa-edit"></i> Edit Feedback
                            </button>
                        <?php endif; ?>
                        
                        <form method="post" action="" id="feedbackForm" class="mt-3 <?php echo ($entry['reviewed'] && !empty($entry['feedback'])) ? 'd-none' : ''; ?>">
                            <input type="hidden" name="action" value="submit_feedback">
                            
                            <div class="mb-3">
                                <label for="feedback" class="form-label">Provide Feedback to Student</label>
                                <textarea class="form-control" id="feedback" name="feedback" rows="5" required><?php echo htmlspecialchars($entry['feedback'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Submit Feedback
                                </button>
                                
                                <?php if ($entry['reviewed'] && !empty($entry['feedback'])): ?>
                                    <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Student Info -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-user-graduate me-1"></i> Student Information
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-2x text-secondary me-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1"><?php echo htmlspecialchars($entry['student_name']); ?></h5>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($entry['student_email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="list-group">
                        <a href="index.php?page=student_details&id=<?php echo $entry['student_id']; ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-id-card me-2"></i> View Profile
                        </a>
                        <a href="index.php?page=student_diary_entries&id=<?php echo $entry['student_id']; ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2"></i> View All Entries
                        </a>
                        <a href="index.php?page=message_student&id=<?php echo $entry['student_id']; ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope me-2"></i> Send Message
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Project Info (if applicable) -->
            <?php if ($project): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-project-diagram me-1"></i> Project Information
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($project['name']); ?></h5>
                    
                    <p class="text-muted">
                        <small>
                            <strong>Period:</strong> <?php echo date('M d, Y', strtotime($project['start_date'])); ?> - 
                            <?php echo date('M d, Y', strtotime($project['end_date'])); ?>
                        </small>
                    </p>
                    
                    <div class="mb-3">
                        <span class="badge bg-<?php 
                            $status = $project['status'] ?? 'pending';
                            switch ($status) {
                                case 'active': echo 'success'; break;
                                case 'pending': echo 'warning'; break;
                                case 'completed': echo 'info'; break;
                                case 'archived': echo 'secondary'; break;
                                default: echo 'primary';
                            }
                        ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </div>
                    
                    <div class="list-group">
                        <a href="index.php?page=view_project&id=<?php echo $project['id']; ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-eye me-2"></i> View Project Details
                        </a>
                        <a href="index.php?page=project_diary_entries&id=<?php echo $project['id']; ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2"></i> All Project Entries
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Navigation -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-link me-1"></i> Quick Navigation
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="index.php?page=teacher_pending_reviews" class="btn btn-warning">
                            <i class="fas fa-clipboard-check me-2"></i> Pending Reviews
                        </a>
                        <a href="index.php?page=teacher_diary_entries" class="btn btn-primary">
                            <i class="fas fa-book me-2"></i> All Diary Entries
                        </a>
                        <a href="index.php?page=teacher_projects" class="btn btn-info">
                            <i class="fas fa-project-diagram me-2"></i> All Projects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit feedback functionality
    const editFeedbackBtn = document.getElementById('editFeedbackBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const feedbackForm = document.getElementById('feedbackForm');
    
    if (editFeedbackBtn) {
        editFeedbackBtn.addEventListener('click', function() {
            editFeedbackBtn.classList.add('d-none');
            feedbackForm.classList.remove('d-none');
        });
    }
    
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            feedbackForm.classList.add('d-none');
            editFeedbackBtn.classList.remove('d-none');
        });
    }
});
</script>