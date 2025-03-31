<?php
// diary_review.php

session_start();
require_once '../../config/database.php';
require_once '../../models/DiaryEntry.php';
require_once '../../models/Notification.php';

$diaryEntryModel = new DiaryEntry();
$notificationModel = new Notification();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../login.php');
    exit();
}

$groupId = $_GET['group_id'] ?? null;
$entries = $diaryEntryModel->getEntriesByGroupId($groupId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entryId = $_POST['entry_id'];
    $feedback = $_POST['feedback'];
    $diaryEntryModel->updateEntryFeedback($entryId, $feedback);
    $notificationModel->createNotification($_SESSION['user_id'], "Feedback provided for entry ID: $entryId");
    header("Location: diary_review.php?group_id=$groupId");
    exit();
}

include '../../includes/header.php';
?>

<div class="container">
    <h2>Diary Review for Group ID: <?php echo htmlspecialchars($groupId); ?></h2>
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Diary Entry</th>
                <th>Feedback</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($entry['content'])); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="entry_id" value="<?php echo $entry['id']; ?>">
                            <textarea name="feedback" rows="2" placeholder="Provide feedback..."></textarea>
                            <button type="submit" class="btn btn-primary">Submit Feedback</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>