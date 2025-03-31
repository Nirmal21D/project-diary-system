<?php
session_start();
include_once '../../config/database.php';
include_once '../../models/DiaryEntry.php';

$database = new Database();
$db = $database->getConnection();

$diaryEntry = new DiaryEntry($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $diaryEntry->user_id = $_SESSION['user_id'];
    $diaryEntry->group_id = $_POST['group_id'];
    $diaryEntry->content = $_POST['content'];
    $diaryEntry->status = 'submitted';

    if ($diaryEntry->create()) {
        echo "<script>alert('Diary entry submitted successfully!');</script>";
    } else {
        echo "<script>alert('Unable to submit diary entry. Please try again.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diary Entry</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include_once '../../includes/header.php'; ?>
    <?php include_once '../../includes/sidebar.php'; ?>

    <div class="container">
        <h2>Submit Diary Entry</h2>
        <form action="diary_entry.php" method="POST">
            <div class="form-group">
                <label for="group_id">Project Group:</label>
                <select name="group_id" required>
                    <!-- Options should be populated from the database -->
                    <option value="">Select Group</option>
                    <?php
                    // Fetch project groups from the database
                    $query = "SELECT id, name FROM project_groups WHERE teacher_id = :teacher_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':teacher_id', $_SESSION['user_id']);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="content">Diary Entry:</label>
                <textarea name="content" rows="10" required></textarea>
            </div>
            <button type="submit">Submit Entry</button>
        </form>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>