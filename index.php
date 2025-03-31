<?php
// Start output buffering
ob_start();

// Include the database configuration file
require_once 'config/database.php';

// Get the database connection
$pdo = getDbConnection();

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for redirect URL in session
if (isset($_SESSION['redirect_url'])) {
    $redirectUrl = $_SESSION['redirect_url'];
    unset($_SESSION['redirect_url']); // Clear it after use
    header("Location: $redirectUrl");
    exit;
}

// Define base path
define('BASE_PATH', __DIR__);
define('BASE_URL', '/project-diary-system'); // Update this as needed

// Include necessary files
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';  // This file creates the $pdo connection
require_once BASE_PATH . '/controllers/AuthController.php';

// Initialize controllers with the database connection
$authController = new AuthController($pdo);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Define $page before using it
$page = $_GET['page'] ?? 'dashboard';

// Fix all dairy/diary typos at once
$diaryPages = ['view_diary_entry', 'diary_entries', 'create_diary_entry', 'edit_diary_entry', 'delete_diary_entry'];
$dairyPages = ['view_dairy_entry', 'dairy_entries', 'add_dairy_entry', 'edit_dairy_entry', 'delete_dairy_entry'];

// Check if the current page is any of the misspelled "dairy" pages
foreach ($dairyPages as $index => $dairyPage) {
    if ($page === $dairyPage) {
        // Redirect to the correctly spelled "diary" page with any parameters
        $correctPage = $diaryPages[$index];
        $params = $_GET;
        unset($params['page']);
        
        $queryString = !empty($params) ? '&' . http_build_query($params) : '';
        header("Location: index.php?page=$correctPage$queryString");
        exit;
    }
}

// If not logged in, redirect to the standalone login.php
if (!$isLoggedIn && $page != 'login' && $page != 'register') {
    header('Location: login.php');
    exit;
}

// Admin routes handling with layout
if ($isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Load admin controller
    require_once BASE_PATH . '/controllers/AdminController.php';
    $adminController = new AdminController($pdo);
    
    // Set default page title
    $pageTitle = 'Admin Dashboard';
    
    switch ($page) {
        case 'dashboard':
            $pageTitle = 'Admin Dashboard';
            $contentView = BASE_PATH . '/views/admin/dashboard.php';
            break;
        case 'manage_users':
            $pageTitle = 'Manage Users';
            $contentView = BASE_PATH . '/views/admin/manage_users.php';
            break;
        case 'add_user':
            $pageTitle = 'Add New User';
            $contentView = BASE_PATH . '/views/admin/add_user.php';
            break;
        case 'edit_user':
            $pageTitle = 'Edit User';
            $contentView = BASE_PATH . '/views/admin/edit_user.php';
            break;
        case 'import_users':
            $pageTitle = 'Import Users';
            $contentView = BASE_PATH . '/views/admin/import_users.php';
            break;
        case 'institutional_info':
            $pageTitle = 'Institutional Information';
            $contentView = BASE_PATH . '/views/admin/institutional_info.php';
            break;
        case 'view_project':
            $pageTitle = 'Project Details';
            $contentView = BASE_PATH . '/views/admin/view_project.php';
            break;
        case 'system_settings':
            $pageTitle = 'System Settings';
            $contentView = BASE_PATH . '/views/admin/system_settings.php';
            break;
        case 'logout':
            session_destroy();
            header('Location: index.php?page=login');
            exit;
            break;
        default:
            $contentView = BASE_PATH . '/views/admin/dashboard.php';
            break;
    }
    
    // Include the admin layout with the content
    include BASE_PATH . '/views/admin/layout.php';
    exit; // Stop further execution
}

// Teacher routes handling with layout
else if ($isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    // Load teacher controller
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
    
    // Set default page title
    $pageTitle = 'Teacher Dashboard';
    
    switch ($page) {
        case 'dashboard':
            $pageTitle = 'Teacher Dashboard';
            $contentView = BASE_PATH . '/views/teacher/dashboard.php';
            break;
        case 'teacher_projects':
            $pageTitle = 'My Projects';
            $contentView = BASE_PATH . '/views/teacher/projects.php';
            break;
        case 'create_project':
            $pageTitle = 'Create New Project';
            $contentView = BASE_PATH . '/views/teacher/create_project.php';
            break;
        case 'view_project':
            $pageTitle = 'Project Details';
            $contentView = BASE_PATH . '/views/teacher/view_project.php';
            break;
        case 'edit_project':
            $pageTitle = 'Edit Project';
            $contentView = BASE_PATH . '/views/teacher/edit_project.php';
            break;
        case 'manage_project_students':
            $pageTitle = 'Manage Project Students';
            $contentView = BASE_PATH . '/views/teacher/manage_project_students.php';
            break;
        case 'project_diary_entries':
            $pageTitle = 'Project Diary Entries';
            $contentView = BASE_PATH . '/views/teacher/project_diary_entries.php';
            break;
        case 'teacher_students':
            $pageTitle = 'My Students';
            $contentView = BASE_PATH . '/views/teacher/students.php';
            break;
        case 'view_student':
            $pageTitle = 'Student Profile';
            $contentView = BASE_PATH . '/views/teacher/view_student.php';
            break;
        case 'student_details':
            $pageTitle = 'Student Details';
            $contentView = BASE_PATH . '/views/teacher/student_details.php';
            break;
        case 'student_diary_entries':
            $pageTitle = 'Student Diary Entries';
            $contentView = BASE_PATH . '/views/teacher/student_diary_entries.php';
            break;
        case 'teacher_diary_entries':
            $pageTitle = 'All Diary Entries';
            $contentView = BASE_PATH . '/views/teacher/diary_entries.php';
            break;
        case 'view_diary_entry':
            $pageTitle = 'View Student Diary Entry';
            $contentView = BASE_PATH . '/views/teacher/view_diary_entry.php';
            break;
       
        case 'teacher_reports':
            $pageTitle = 'Reports';
            $contentView = BASE_PATH . '/views/teacher/reports.php';
            break;
        case 'teacher_schedule':
            $pageTitle = 'Schedule';
            $contentView = BASE_PATH . '/views/teacher/schedule.php';
            break;
        case 'message_student':
            $pageTitle = 'Message Student';
            $contentView = BASE_PATH . '/views/teacher/message_student.php';
            break;
        case 'fix_teacher_role':
            $teacherId = $_POST['user_id'] ?? $_SESSION['user_id'] ?? 0;
            if ($teacherController->fixTeacherRole($teacherId)) {
                $_SESSION['role'] = 'teacher';
                $_SESSION['success_message'] = "Teacher role has been fixed!";
            } else {
                $_SESSION['error_message'] = "Failed to fix teacher role.";
            }
            header('Location: index.php?page=create_project');
            exit;
            break;
        case 'create_tables':
            if ($teacherController->ensureTablesExist()) {
                $_SESSION['success_message'] = "Database tables have been created!";
            } else {
                $_SESSION['error_message'] = "Failed to create some tables.";
            }
            header('Location: index.php?page=create_project');
            exit;
            break;
        case 'review_diary_entry':
            $pageTitle = 'Review Diary Entry';
            $contentView = BASE_PATH . '/views/teacher/review_diary_entry.php';
            break;
        case 'logout':
            session_destroy();
            header('Location: index.php?page=login');
            exit;
            break;
        default:
            $contentView = BASE_PATH . '/views/teacher/dashboard.php';
            break;
    }
    
    // Include the teacher layout with the content
    include BASE_PATH . '/views/teacher/layout.php';
    exit; // Stop further execution
}

// Student routes handling with layout
else if ($isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    // Load student controller
    require_once BASE_PATH . '/controllers/StudentController.php';
    $studentController = new StudentController($pdo);
    
    // Set default page title and content view
    $pageTitle = 'Student Dashboard';
    $contentView = BASE_PATH . '/views/student/dashboard.php';
    
    // Determine which page to display
    switch ($page) {
        case 'dashboard':
            $pageTitle = 'Student Dashboard';
            $contentView = BASE_PATH . '/views/student/dashboard.php';
            break;
        case 'student_projects':
            $pageTitle = 'My Projects';
            $contentView = BASE_PATH . '/views/student/projects.php';
            break;
        case 'view_project':
            $pageTitle = 'Project Details';
            $contentView = BASE_PATH . '/views/student/view_project.php';
            break;
        case 'diary_entries':
            $pageTitle = 'My Diary Entries';
            $contentView = BASE_PATH . '/views/student/diary_entries.php';
            break;
        case 'view_diary_entry':
            $pageTitle = 'View Diary Entry';
            $contentView = BASE_PATH . '/views/student/view_diary_entry.php';
            break;
        case 'create_diary_entry':
            $pageTitle = 'Create Diary Entry';
            $contentView = BASE_PATH . '/views/student/create_diary_entry.php';
            break;
        case 'edit_diary_entry':
            $pageTitle = 'Edit Diary Entry';
            $contentView = BASE_PATH . '/views/student/edit_diary_entry.php';
            break;
        case 'create_diary_entry':
            $pageTitle = 'Add Diary Entry';
            $contentView = BASE_PATH . '/views/student/create_diary_entry.php';
            break;
        case 'pending_reviews':
            $pageTitle = 'Pending Reviews';
            $contentView = BASE_PATH . '/views/student/pending_reviews.php';
            break;
        
        case 'profile':
            $pageTitle = 'My Profile';
            $contentView = BASE_PATH . '/views/student/profile.php';
            break;
        case 'settings':
            $pageTitle = 'Settings';
            $contentView = BASE_PATH . '/views/student/settings.php';
            break;
        case 'logout':
            session_destroy();
            header('Location: index.php?page=login');
            exit;
    }
    
    // Include the layout file which will include the appropriate content view
    include BASE_PATH . '/views/student/layout.php';
    exit; // Stop further execution
}

// Non-admin routes (existing code)
switch ($page) {
    // Authentication routes
    case 'login':
        header('Location: login.php');
        exit;
        break;
    case 'register':
        include BASE_PATH . '/views/register.php';
        break;
    case 'logout':
        // Destroy session and redirect to login page
        session_destroy();
        header('Location: index.php?page=login');
        exit;
        break;
        
    // Other role dashboards
    case 'dashboard':
        if (!$isLoggedIn) {
            header('Location: index.php?page=login');
            exit;
        }
        
        // Redirect to appropriate dashboard based on user role
        switch ($_SESSION['role']) {
            case 'teacher':
                include BASE_PATH . '/views/teacher/dashboard.php';
                break;
            case 'student':
                include BASE_PATH . '/views/student/dashboard.php';
                break;
            default:
                include BASE_PATH . '/views/errors/403.php';
                break;
        }
        break;
        
    // Default case and home page
    case 'home':
    default:
        if (!$isLoggedIn) {
            header('Location: login.php');
            exit;
        } else {
            // Redirect to appropriate dashboard
            header('Location: index.php?page=dashboard');
            exit;
        }
        break;
}

// Include footer for non-admin pages
include BASE_PATH . '/includes/footer.php';

// End output buffering
ob_end_flush();
?>