<?php
// Get entry ID from URL
$entryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacherId = $_SESSION['user_id'] ?? 0;

// Validate parameters
if (!$entryId) {
    $errorMessage = "Invalid diary entry ID";
} else {
    try {
        // Get diary entry details - teachers can view any student's entries
        $entry = $teacherController->getDiaryEntry($entryId);
        
        if (!$entry) {
            $errorMessage = "Diary entry not found";
        } else {
            // Format date for display
            $formattedDate = date('F j, Y', strtotime($entry['entry_date']));
            
            // Get student details
            $student = $teacherController->getStudentById($entry['user_id']);
            
            // Get project details
            $project = $teacherController->getProjectById($entry['project_group_id']);
        }
    } catch (Exception $e) {
        $errorMessage = "Error loading diary entry: " . $e->getMessage();
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Diary Entry Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=student_diary_entries">Student Diary Entries</a></li>
        <li class="breadcrumb-item active">View Entry</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <div class="mb-3">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
    <?php else: ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-book me-1"></i> 
                        <?php echo htmlspecialchars($entry['title']); ?>
                    </div>
                    <div>
                        <?php if (isset($entry['reviewed']) && $entry['reviewed']): ?>
                            <span class="badge bg-success">Reviewed</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Pending Review</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Student:</div>
                        <div class="col-md-9">
                            <?php if (isset($student['name'])): ?>
                                <a href="index.php?page=view_student&id=<?php echo $entry['user_id']; ?>">
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </a>
                            <?php else: ?>
                                <?php echo htmlspecialchars("Unknown Student (ID: {$entry['user_id']})"); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Date:</div>
                        <div class="col-md-9"><?php echo $formattedDate; ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Project:</div>
                        <div class="col-md-9">
                            <?php if (isset($project['name'])): ?>
                                <a href="index.php?page=view_project&id=<?php echo $entry['project_group_id']; ?>">
                                    <?php echo htmlspecialchars($project['name']); ?>
                                </a>
                            <?php else: ?>
                                <?php echo htmlspecialchars("Unknown Project (ID: {$entry['project_group_id']})"); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Time Spent:</div>
                        <div class="col-md-9">
                            <?php 
                                if (isset($entry['hours_spent'])) {
                                    $hoursSpent = $entry['hours_spent'];
                                    if (is_string($hoursSpent) && strpos($hoursSpent, ':') !== false) {
                                        list($hours, $minutes) = explode(':', $hoursSpent);
                                        echo $hours . ' hour(s) ' . $minutes . ' minute(s)';
                                    } else {
                                        echo $hoursSpent;
                                    }
                                } else {
                                    echo 'Not specified';
                                }
                            ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 fw-bold">Content:</div>
                        <div class="col-md-9">
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($entry['content'] ?? '')); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!isset($entry['reviewed']) || !$entry['reviewed']): ?>
                        <div class="border-top pt-3">
                            <h5>Provide Feedback</h5>
                            <form method="post" action="index.php?page=review_diary_entry&id=<?php echo $entryId; ?>">
                                <div class="mb-3">
                                    <label for="feedback" class="form-label">Feedback <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="feedback" name="feedback" rows="5" required></textarea>
                                    <div class="form-text">Provide constructive feedback on the student's work and progress.</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Submit Feedback
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="border-top pt-3">
                            <h5>Feedback</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?php 
                                        if (isset($entry['reviewed_at'])) {
                                            echo "Provided on " . date('F j, Y', strtotime($entry['reviewed_at']));
                                        } 
                                        ?>
                                    </h6>
                                    <p class="card-text">
                                        <?php echo nl2br(htmlspecialchars($entry['feedback'] ?? 'No feedback provided')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="javascript:history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i> Entry Information
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Created
                            <span><?php echo isset($entry['created_at']) ? date('M j, Y g:i A', strtotime($entry['created_at'])) : 'N/A'; ?></span>
                        </li>
                        
                        <?php if (isset($entry['updated_at']) && isset($entry['created_at']) && $entry['updated_at'] != $entry['created_at']): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Last Updated
                            <span><?php echo date('M j, Y g:i A', strtotime($entry['updated_at'])); ?></span>
                        </li>
                        <?php endif; ?>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Status
                            <?php if (isset($entry['reviewed']) && $entry['reviewed']): ?>
                                <span class="badge bg-success rounded-pill">Reviewed</span>
                            <?php else: ?>
                                <span class="badge bg-warning rounded-pill">Pending Review</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>