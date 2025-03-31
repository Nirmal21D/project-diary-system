<?php
// Get entry ID from URL
$entryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$studentId = $_SESSION['user_id'] ?? 0;

// Debug output (remove after troubleshooting)
// echo "Entry ID: $entryId, Student ID: $studentId";

try {
    // Get diary entry details
    $entry = $studentController->getDiaryEntry($entryId, $studentId);
    
    if (!$entry) {
        echo "<div class='alert alert-danger'>Entry not found or you don't have permission to view it.</div>";
        echo "<script>setTimeout(function() { window.location.href = 'index.php?page=diary_entries'; }, 3000);</script>";
        // Don't use exit here - it will prevent the layout from rendering
    }
    
    // Format date for display if entry exists
    if ($entry) {
        $formattedDate = date('F j, Y', strtotime($entry['entry_date']));
    }
} catch (Exception $e) {
    $errorMessage = "Error loading diary entry: " . $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Diary Entry Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=diary_entries">Diary Entries</a></li>
        <li class="breadcrumb-item active">View Entry</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($entry)): ?>
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
                                <?php echo htmlspecialchars($entry['project_name'] ?? 'Unknown Project'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Time Spent:</div>
                        <div class="col-md-9">
                            <?php 
                                // Format hours spent properly based on your data structure
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
                    
                    <?php if (isset($entry['reviewed']) && $entry['reviewed'] && isset($entry['feedback'])): ?>
                    <hr>
                    <div class="row mt-4">
                        <div class="col-md-3 fw-bold">Feedback:</div>
                        <div class="col-md-9">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Reviewed by <?php echo htmlspecialchars($entry['reviewer_name'] ?? 'Teacher'); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?php echo isset($entry['reviewed_at']) ? date('F j, Y', strtotime($entry['reviewed_at'])) : date('F j, Y'); ?>
                                    </h6>
                                    <p class="card-text">
                                        <?php echo nl2br(htmlspecialchars($entry['feedback'] ?? 'No feedback provided')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <?php if (!isset($entry['reviewed']) || !$entry['reviewed']): ?>
                            <a href="index.php?page=edit_diary_entry&id=<?php echo $entry['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteEntryModal">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        <?php endif; ?>
                        <a href="index.php?page=diary_entries" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Entries
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
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEntryModal" tabindex="-1" aria-labelledby="deleteEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEntryModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this diary entry? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="index.php?page=delete_diary_entry&id=<?php echo $entry['id']; ?>" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Entry not found or you don't have permission to view it.
            <a href="index.php?page=diary_entries" class="alert-link">Return to diary entries</a>
        </div>
    <?php endif; ?>
</div>