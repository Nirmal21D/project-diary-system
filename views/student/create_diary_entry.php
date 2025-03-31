<?php
// filepath: c:\xampp\htdocs\project-diary-system\views\student\create_diary_entry.php

// Enable output buffering at the top of the file
ob_start();

// Get student ID from session
$studentId = $_SESSION['user_id'] ?? 0;

// Get project group ID from URL if available (for pre-selected project)
$preSelectedProjectId = $_GET['project_id'] ?? null;

// Get projects for dropdown (using correct method)
$projects = $studentController->getStudentProjects($studentId);

// Initialize form data
$formData = [
    'project_id' => $preSelectedProjectId,
    'title' => '',
    'content' => '',
    'date' => date('Y-m-d'),
    'hours_spent' => '01:00:00' // Default 1 hour in HH:MM:SS format
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $projectId = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $hoursSpent = filter_input(INPUT_POST, 'hours_spent', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Update form data for re-display if needed
    $formData = [
        'project_id' => $projectId,
        'title' => $_POST['title'] ?? '',
        'content' => $_POST['content'] ?? '',
        'date' => $_POST['date'] ?? date('Y-m-d'),
        'hours_spent' => $_POST['hours_spent'] ?? '01:00:00'
    ];
    
    // Validate required fields
    $errors = [];
    
    if (empty($projectId)) {
        $errors[] = "Please select a project.";
    }
    
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
            // Create diary entry - IMPORTANT: $projectId here is actually the project_group_id
            $entryId = $studentController->createDiaryEntry($studentId, $projectId, $title, $content, $date, $hoursSpent);
            
            if ($entryId) {
                $_SESSION['success_message'] = "Diary entry created successfully!";
                
                // Store redirect URL in session instead of using header()
                $_SESSION['redirect_url'] = "index.php?page=view_diary_entry&id=$entryId";
                
                // Use JavaScript redirect instead of header()
                echo "<script>window.location.href = 'index.php?page=view_diary_entry&id=$entryId';</script>";
                // No exit here - let the script continue
            } else {
                $errorMessage = $studentController->getLastError() ?? "Unknown error creating diary entry";
            }
        } catch (Exception $e) {
            $errorMessage = "Error creating diary entry: " . $e->getMessage();
        }
    } else {
        $errorMessage = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Create Diary Entry</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=diary_entries">Diary Entries</a></li>
        <li class="breadcrumb-item active">Create Entry</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-triangle me-2"></i> Error</h5>
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i> New Diary Entry
        </div>
        <div class="card-body">
            <form method="post" action="index.php?page=create_diary_entry" id="diaryEntryForm">
                <?php if ($preSelectedProjectId): ?>
                    <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($preSelectedProjectId); ?>">
                <?php else: ?>
                <div class="mb-3">
                    <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                    <select class="form-select" id="project_id" name="project_id" required>
                        <option value="" selected disabled>-- Select Project --</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" <?php echo ($formData['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Select the project this entry belongs to</div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($formData['title']); ?>" required minlength="5">
                    <div class="form-text">A brief title describing your work (min 5 characters)</div>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="10" required minlength="20"><?php echo htmlspecialchars($formData['content']); ?></textarea>
                    <div class="form-text">Describe what you worked on, challenges faced, and solutions implemented (min 20 characters)</div>
                </div>
                
                <div class="mb-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($formData['date']); ?>" required max="<?php echo date('Y-m-d'); ?>">
                    <div class="form-text">The date this work was done (cannot be in the future)</div>
                </div>
                
                <div class="mb-3">
                    <label for="hours_spent" class="form-label">Time Spent <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="hours_spent" name="hours_spent" value="<?php echo htmlspecialchars($formData['hours_spent']); ?>" required>
                    <div class="form-text">How many hours and minutes did you spend on this task (HH:MM)</div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Entry
                </button>
                <a href="index.php?page=diary_entries" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Basic validation for the form
    const form = document.getElementById('diaryEntryForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const projectSelect = document.getElementById('project_id');
            
            if (projectSelect && projectSelect.value === "") {
                isValid = false;
                alert('Please select a project');
            }
            
            if (title.length < 5) {
                isValid = false;
                alert('Title must be at least 5 characters');
            }
            
            if (content.length < 20) {
                isValid = false;
                alert('Content must be at least 20 characters');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});
</script>