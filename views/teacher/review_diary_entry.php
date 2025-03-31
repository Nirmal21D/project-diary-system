<?php
// Get parameters
$entryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacherId = $_SESSION['user_id'] ?? 0;

// Validate entry ID
if (!$entryId) {
    $_SESSION['error_message'] = "Invalid diary entry ID";
    echo "<script>window.location.href = 'index.php?page=student_diary_entries';</script>";
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize feedback
    $feedback = trim(filter_input(INPUT_POST, 'feedback', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    // Validate feedback
    if (empty($feedback)) {
        $_SESSION['error_message'] = "Feedback cannot be empty";
        echo "<script>window.location.href = 'index.php?page=view_diary_entry&id=$entryId';</script>";
        exit;
    }
    
    try {
        // Submit the review
        $result = $teacherController->reviewDiaryEntry($entryId, $feedback, $teacherId);
        
        if ($result) {
            $_SESSION['success_message'] = "Feedback submitted successfully";
        } else {
            $_SESSION['error_message'] = "Error submitting feedback";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    
    // Redirect back to the entry view
    echo "<script>window.location.href = 'index.php?page=view_diary_entry&id=$entryId';</script>";
    exit;
}

// If accessed directly without POST data, redirect to the entry view
echo "<script>window.location.href = 'index.php?page=view_diary_entry&id=$entryId';</script>";
exit;
?>