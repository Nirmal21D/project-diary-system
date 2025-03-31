<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Project Diary System' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open me-2"></i>
                Project Diary
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=manage_users"><i class="fas fa-users me-1"></i> Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=institution_info"><i class="fas fa-university me-1"></i> Institution Info</a></li>
                    <?php elseif ($_SESSION['role'] == 'teacher'): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=groups"><i class="fas fa-users me-1"></i> Project Groups</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=diaries"><i class="fas fa-book me-1"></i> Diaries</a></li>
                    <?php elseif ($_SESSION['role'] == 'student'): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=my_projects"><i class="fas fa-tasks me-1"></i> My Projects</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=diary"><i class="fas fa-edit me-1"></i> Project Diary</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-id-card me-1"></i> My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a></li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 main-content">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
                <?= $_SESSION['flash_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>