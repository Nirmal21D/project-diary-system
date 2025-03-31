<?php 
// Start output buffering at the very beginning
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Project Diary System" />
    <meta name="author" content="Your Name" />
    <title><?php echo $pageTitle ?? 'Student Dashboard'; ?> - Project Diary System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Simple DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    
    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    
    <!-- Custom Styles -->
    <link href="assets/css/styles.css" rel="stylesheet" />
    
    <style>
        /* Custom styles for student dashboard */
        :root {
            --student-primary: #4caf50;
            --student-secondary: #81c784;
            --student-light: #e8f5e9;
            --student-dark: #2e7d32;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Left sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #212529;
            width: 250px;
            transition: all 0.3s;
        }
        
        .sidebar-sticky {
            position: sticky;
            top: 0;
            height: calc(100vh);
            padding-top: 0.5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(76, 175, 80, 0.25);
            border-left: 4px solid var(--student-primary);
        }
        
        .sidebar .nav-link .icon {
            margin-right: 10px;
            color: rgba(255, 255, 255, 0.5);
            width: 20px;
            text-align: center;
        }
        
        .sidebar .nav-link.active .icon {
            color: var(--student-primary);
        }
        
        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 1rem 1rem 0.5rem;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Main content area */
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        /* Top navigation bar */
        .top-navbar {
            background-color: #343a40;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            padding: 0.5rem 1rem;
            z-index: 99;
            position: sticky;
            top: 0;
        }
        
        .top-navbar .navbar-brand {
            font-weight: bold;
            color: var(--student-primary);
        }
        
        /* Collapsed sidebar state */
        body.sidebar-collapsed .sidebar {
            width: 60px;
            text-align: center;
        }
        
        body.sidebar-collapsed .sidebar .nav-link {
            padding: 0.75rem 0.5rem;
            justify-content: center;
        }
        
        body.sidebar-collapsed .sidebar .nav-link .icon {
            margin-right: 0;
            font-size: 1.2rem;
        }
        
        body.sidebar-collapsed .sidebar .nav-text,
        body.sidebar-collapsed .sidebar .sidebar-heading,
        body.sidebar-collapsed .sidebar .sidebar-footer-text {
            display: none;
        }
        
        body.sidebar-collapsed .main-content {
            margin-left: 60px;
        }
        
        /* Sidebar footer */
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem;
            background-color: rgba(0, 0, 0, 0.2);
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }
        
        /* Custom buttons */
        .btn-student {
            background-color: var(--student-primary);
            border-color: var(--student-primary);
            color: white;
        }
        
        .btn-student:hover {
            background-color: var(--student-dark);
            border-color: var(--student-dark);
            color: white;
        }
        
        /* User avatar */
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--student-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        /* Card headers with student theme */
        .card-header-student {
            background-color: var(--student-light);
            color: var(--student-dark);
            border-bottom: 1px solid rgba(76, 175, 80, 0.2);
        }
        
        /* Logo area */
        .logo-container {
            padding: 1rem;
            background-color: #1a1e21;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }
        
        .logo-container .logo-text {
            color: var(--student-primary);
            font-weight: bold;
            font-size: 1.2rem;
            margin-left: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            .sidebar .nav-link {
                padding: 0.75rem 0.5rem;
                justify-content: center;
            }
            .sidebar .nav-link .icon {
                margin-right: 0;
                font-size: 1.2rem;
            }
            .sidebar .nav-text,
            .sidebar .sidebar-heading,
            .sidebar .sidebar-footer-text {
                display: none;
            }
            .main-content {
                margin-left: 60px;
            }
            
            body.sidebar-expanded .sidebar {
                width: 250px;
            }
            body.sidebar-expanded .sidebar .nav-link {
                padding: 0.75rem 1rem;
                justify-content: flex-start;
            }
            body.sidebar-expanded .sidebar .nav-link .icon {
                margin-right: 10px;
                font-size: 1rem;
            }
            body.sidebar-expanded .sidebar .nav-text,
            body.sidebar-expanded .sidebar .sidebar-heading,
            body.sidebar-expanded .sidebar .sidebar-footer-text {
                display: block;
            }
            body.sidebar-expanded .main-content {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Left Sidebar -->
    <div class="sidebar">
        <!-- Logo Section -->
        <div class="logo-container">
            <i class="fas fa-book-reader fa-2x" style="color: #4caf50;"></i>
            <span class="logo-text nav-text">Project Diary</span>
        </div>
        
        <div class="sidebar-sticky">
            <!-- Main Navigation -->
            <ul class="nav flex-column">
                <li class="sidebar-heading nav-text">Main</li>
                
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=dashboard">
                        <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                
                <li class="sidebar-heading nav-text">Projects</li>
                
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=student_projects">
                        <span class="icon"><i class="fas fa-project-diagram"></i></span>
                        <span class="nav-text">My Projects</span>
                    </a>
                </li>
                
                <li class="sidebar-heading nav-text">Diary</li>
                
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=view_diary_entry">
                        <span class="icon"><i class="fas fa-book"></i></span>
                        <span class="nav-text">All Entries</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=create_diary_entry">
                        <span class="icon"><i class="fas fa-plus-circle"></i></span>
                        <span class="nav-text">Create Entry</span>
                    </a>
                </li>
                
                <li class="sidebar-heading nav-text">Account</li>
                
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=profile">
                        <span class="icon"><i class="fas fa-user"></i></span>
                        <span class="nav-text">My Profile</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=settings">
                        <span class="icon"><i class="fas fa-cog"></i></span>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=logout">
                        <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="d-flex align-items-center">
                <div class="user-avatar">
                    <?php
                    $name = $_SESSION['name'] ?? 'Student';
                    echo strtoupper(substr($name, 0, 1));
                    ?>
                </div>
                <div class="sidebar-footer-text">
                    <small>Logged in as</small><br>
                    <span><?php echo htmlspecialchars($name); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar top-navbar">
            <div class="container-fluid">
                <button class="btn btn-link sidebar-toggler" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <span class="navbar-text d-none d-md-inline">
                    Student Portal
                </span>
                
                <ul class="navbar-nav ms-auto d-flex flex-row">
                    <!-- Help -->
                    <li class="nav-item me-3">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#helpModal">
                            <i class="fas fa-question-circle"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Page Content -->
        <main class="container-fluid p-4">
            <?php 
            if (isset($contentView)) {
                if (file_exists($contentView)) {
                    include $contentView;
                } else {
                    echo '<div class="alert alert-danger">';
                    echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                    echo 'Content file not found: ' . htmlspecialchars($contentView);
                    echo '</div>';
                }
            } else {
                echo '<div class="alert alert-danger">';
                echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                echo 'No content view specified.';
                echo '</div>';
            }
            ?>
        </main>
        
        <!-- Footer -->
        <footer class="bg-light p-3 border-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 small">
                        &copy; <?php echo date('Y'); ?> Project Diary System
                    </div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#" class="text-decoration-none">Privacy Policy</a>
                        &middot;
                        <a href="#" class="text-decoration-none">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Help & Support</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-book-open me-2"></i> <a href="#">User Guide</a></li>
                        <li><i class="fas fa-question-circle me-2"></i> <a href="#">FAQs</a></li>
                        <li><i class="fas fa-envelope me-2"></i> <a href="#">Contact Support</a></li>
                    </ul>
                    
                    <h6 class="mt-4">Contact Teacher</h6>
                    <p>If you're having issues with your projects or need help, please contact your teacher directly.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    
    <!-- Custom Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight active menu item
        const currentPage = window.location.search.split('page=')[1] || 'dashboard';
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.includes(currentPage)) {
                link.classList.add('active');
            }
        });
        
        // Toggle sidebar
        const sidebarToggle = document.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
                
                // Save preference
                if (document.body.classList.contains('sidebar-collapsed')) {
                    localStorage.setItem('sidebar-collapsed', 'true');
                } else {
                    localStorage.setItem('sidebar-collapsed', 'false');
                }
            });
        }
        
        // Check for saved preference
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }
        
        // Initialize any DataTables
        const dataTables = document.querySelectorAll('.datatable');
        if (dataTables.length > 0) {
            dataTables.forEach(table => {
                new simpleDatatables.DataTable(table);
            });
        }
    });
    </script>
</body>
</html>
<?php
// Flush the output buffer at the end
ob_end_flush();
?>