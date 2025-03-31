<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Teacher Dashboard'; ?> - Project Diary System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="assets/css/teacher-style.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?page=dashboard">Project Diary System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="topNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['name'] ?? 'Teacher'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-user-cog"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="list-group list-group-flush">
                        <a href="index.php?page=dashboard" class="list-group-item list-group-item-action <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        
                        <a href="index.php?page=teacher_projects" class="list-group-item list-group-item-action <?php echo $page === 'teacher_projects' || $page === 'create_project' ? 'active' : ''; ?>">
                            <i class="fas fa-project-diagram"></i> My Projects
                        </a>
                        
                        <a href="index.php?page=teacher_students" class="list-group-item list-group-item-action <?php echo $page === 'teacher_students' ? 'active' : ''; ?>">
                            <i class="fas fa-user-graduate"></i> My Students
                        </a>
                        
                        <a href="index.php?page=teacher_diary_entries" class="list-group-item list-group-item-action <?php echo $page === 'teacher_diary_entries' || $page === 'view_diary_entry' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i> Diary Entries
                        </a>
                        
                        <a href="index.php?page=teacher_pending_reviews" class="list-group-item list-group-item-action <?php echo $page === 'teacher_pending_reviews' ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-check"></i> Pending Reviews
                        </a>
                        
                        <a href="index.php?page=logout" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1><?php echo isset($pageTitle) ? $pageTitle : 'Teacher Dashboard'; ?></h1>
                </div>
                
                <!-- Content will be injected here -->
                <?php 
                if (isset($contentView) && file_exists($contentView)) {
                    include $contentView;
                }
                ?>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© <?php echo date('Y'); ?> Project Diary System</span>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (if needed) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom scripts -->
    <script src="assets/js/teacher-script.js"></script>
</body>
</html>