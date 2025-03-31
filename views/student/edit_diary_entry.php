<?php
// Get entry ID from URL
$entryId = $_GET['id'] ?? 0;
$studentId = $_SESSION['user_id'] ?? 0;

// Initialize form data
$formData = [
    'title' => '',
    'content' => '',
    'date' => date('Y-m-d'),
    'hours_spent' => '01:00'
];

// Validate permissions
if (!$entryId || !$studentId) {
    $_SESSION['error_message'] = "Invalid request";
    header('Location: index.php?page=diary_entries');
    exit;
}

try {
    // Load entry data
    $entry = $studentController->getDiaryEntry($entryId, $studentId);
    
    // Check if entry exists and belongs to this student
    if (!$entry) {
        $_SESSION['error_message'] = "Diary entry not found or you don't have permission to edit it";
        header('Location: index.php?page=diary_entries');
        exit;
    }
    
    // Check if entry has been reviewed (can't edit reviewed entries)
    if (isset($entry['reviewed']) && $entry['reviewed']) {
        $_SESSION['error_message'] = "You cannot edit an entry that has already been reviewed";
        header('Location: index.php?page=view_diary_entry&id=' . $entryId);
        exit;
    }
    
    // Format hours_spent from database format to input format
    $hoursSpent = $entry['hours_spent'] ?? '01:00:00';
    if (strpos($hoursSpent, ':') !== false) {
        // Format hours:minutes:seconds to hours:minutes for the time input
        $parts = explode(':', $hoursSpent);
        $hoursSpent = sprintf('%02d:%02d', $parts[0], $parts[1]);
    }
    
    // Load form data from entry
    $formData = [
        'title' => $entry['title'] ?? '',
        'content' => $entry['content'] ?? '',
        'date' => $entry['entry_date'] ?? date('Y-m-d'),
        'hours_spent' => $hoursSpent
    ];
    
    // Get project information
    $projectGroupId = $entry['project_group_id'] ?? 0;
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error loading diary entry: " . $e->getMessage();
    header('Location: index.php?page=diary_entries');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $hoursSpent = filter_input(INPUT_POST, 'hours_spent', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Update form data for re-display if needed
    $formData = [
        'title' => $title,
        'content' => $content,
        'date' => $date,
        'hours_spent' => $hoursSpent
    ];
    
    // Validate required fields
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Entry title is required.";
    } elseif (strlen($title) < 5) {
        $errors[] = "Entry title must be at least 5 characters.";
    }
    
    if (empty($content)) {
        $errors[] = "Entry content is required.";
    } elseif (strlen($content) < 20) {
        $errors[] = "Please provide more details (at least 20 characters).";
    }
    
    if (empty($date)) {
        $errors[] = "Entry date is required.";
    }
    
    if (empty($hoursSpent)) {
        $errors[] = "Hours spent must be provided.";
    }
    
    // If no validation errors
    if (empty($errors)) {
        try {
            // Format hours_spent for database storage if needed
            // If database expects HH:MM:SS format but input is HH:MM
            if (substr_count($hoursSpent, ':') === 1) {
                $hoursSpent .= ':00';
            }
            
            // Update diary entry
            $updated = $studentController->updateDiaryEntry($entryId, $studentId, $title, $content, $date, $hoursSpent);
            
            if ($updated) {
                $_SESSION['success_message'] = "Diary entry updated successfully!";
                header('Location: index.php?page=view_diary_entry&id=' . $entryId);
                exit;
            } else {
                $errorMessage = $studentController->getLastError() ?? "Unknown error updating diary entry";
            }
        } catch (Exception $e) {
            $errorMessage = "Error updating diary entry: " . $e->getMessage();
        }
    } else {
        $errorMessage = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Diary Entry</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=diary_entries">Diary Entries</a></li>
        <li class="breadcrumb-item active">Edit Entry</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i> Edit Entry for <?php echo htmlspecialchars($entry['project_name'] ?? 'Unknown Project'); ?>
        </div>
        <div class="card-body">
            <form method="post" action="index.php?page=edit_diary_entry&id=<?php echo $entryId; ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($formData['title']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($formData['content']); ?></textarea>
                    <div class="form-text">Provide details about what you worked on, challenges faced, and solutions implemented.</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo $formData['date']; ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="hours_spent" class="form-label">Time Spent <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="hours_spent" name="hours_spent" value="<?php echo $formData['hours_spent']; ?>" step="60" required>
                        <div class="form-text">Format: hours:minutes (e.g., 01:30 for 1 hour and 30 minutes)</div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Entry</button>
                    <a href="index.php?page=view_diary_entry&id=<?php echo $entryId; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>