<?php
// groups.php

include_once '../../config/database.php';
include_once '../../models/ProjectGroup.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$projectGroup = new ProjectGroup($db);
$groups = $projectGroup->getGroupsByTeacherId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Project Groups</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include_once '../../includes/header.php'; ?>
    <?php include_once '../../includes/sidebar.php'; ?>

    <div class="container">
        <h1>Manage Project Groups</h1>
        <a href="create_group.php" class="btn">Create New Group</a>
        <table>
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($group['name']); ?></td>
                        <td>
                            <a href="edit_group.php?id=<?php echo $group['id']; ?>" class="btn">Edit</a>
                            <a href="delete_group.php?id=<?php echo $group['id']; ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>