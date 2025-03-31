<?php
session_start();
include_once '../../config/database.php';
include_once '../../models/User.php';
include_once '../../models/DiaryEntry.php';
include_once '../../models/Notification.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$diaryEntry = new DiaryEntry($db);
$notification = new Notification($db);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$diaryEntries = $diaryEntry->getEntriesByUserId($userId);
$notifications = $notification->getUserNotifications($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include_once '../../includes/header.php'; ?>
    <?php include_once '../../includes/sidebar.php'; ?>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
        
        <h2>Your Diary Entries</h2>
        <table>
            <thead>
                <tr>
                    <th>Entry ID</th>
                    <th>Content</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($diaryEntries as $entry): ?>
                    <tr>
                        <td><?php echo $entry['id']; ?></td>
                        <td><?php echo htmlspecialchars($entry['content']); ?></td>
                        <td><?php echo htmlspecialchars($entry['status']); ?></td>
                        <td>
                            <a href="diary_entry.php?id=<?php echo $entry['id']; ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Notifications</h2>
        <ul>
            <?php foreach ($notifications as $notification): ?>
                <li><?php echo htmlspecialchars($notification['message']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>