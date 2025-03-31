<?php
// Get entry ID from URL
$entryId = $_GET['id'] ?? 0;
$studentId = $_SESSION['user_id'] ?? 0;

// Validate permissions
try {
    $entry = $studentController->getDiaryEntry($entryId, $studentId);
    
    if (!$entry) {
        $_SESSION['error_message'] = "Entry not found or you don't have permission to delete it";
        header("Location: index.php?page=diary_entries");
        exit;
    }
    
    // Don't allow deleting reviewed entries
    if ($entry['reviewed']) {
        $_SESSION['error_message'] = "You cannot delete entries that have already been reviewed";
        header("Location: index.php?page=view_diary_entry&id=$entryId");
        exit;
    }
    
    // Process deletion if confirmed
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        $deleted = $studentController->deleteDiaryEntry($entryId, $studentId);
        
        if ($deleted) {
            $_SESSION['success_message'] = "Diary entry deleted successfully";
            header("Location: index.php?page=diary_entries");
            exit;
        } else {
            $errorMessage = "Failed to delete the entry";
        }
    }
} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Delete Diary Entry</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=diary_entries">Diary Entries</a></li>
        <li class="breadcrumb-item active">Delete Entry</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <i class="fas fa-exclamation-triangle me-1"></i> Confirm Deletion
        </div>
        <div class="card-body">
            <p class="fs-5">Are you sure you want to delete the following diary entry?</p>
            
            <div class="alert alert-warning">
                <h5><?php echo htmlspecialchars($entry['title'] ?? ''); ?></h5>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($entry['entry_date'] ?? 'now')); ?></p>
                <p><strong>Project:</strong> <?php echo htmlspecialchars($entry['project_name'] ?? ''); ?></p>
            </div>
            
            <p class="text-danger fw-bold">This action cannot be undone!</p>
            
            <form method="post" action="index.php?page=delete_diary_entry&id=<?php echo $entryId; ?>">
                <input type="hidden" name="confirm_delete" value="yes">
                <button type="submit" class="btn btn-danger">Yes, Delete Entry</button>
                <a href="index.php?page=view_diary_entry&id=<?php echo $entryId; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>