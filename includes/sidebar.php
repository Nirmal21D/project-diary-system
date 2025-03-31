<?php
session_start();

// Sidebar navigation for different user roles
function renderSidebar($role) {
    echo '<div class="sidebar">';
    
    if ($role === 'admin') {
        echo '<div class="list-group list-group-flush">';
        echo '<ul>';
        echo '<li><a href="views/admin/dashboard.php">Dashboard</a></li>';
        echo '<li><a href="views/admin/manage_users.php">Manage Users</a></li>';
        echo '<li><a href="views/admin/import_users.php">Import Users</a></li>';
        echo '</ul>';
        echo '</div>';
    } elseif ($role === 'teacher') {
        echo '<ul>';
        echo '<li><a href="views/teacher/dashboard.php">Dashboard</a></li>';
        echo '<li><a href="views/teacher/groups.php">Manage Groups</a></li>';
        echo '<li><a href="views/teacher/diary_review.php">Review Diaries</a></li>';
        echo '</ul>';
    } elseif ($role === 'student') {
        echo '<ul>';
        echo '<li><a href="views/student/dashboard.php">Dashboard</a></li>';
        echo '<li><a href="views/student/diary_entry.php">Diary Entry</a></li>';
        echo '</ul>';
    }
    
    echo '</div>';
}

// Call the function to render the sidebar based on user role
if (isset($_SESSION['user_role'])) {
    renderSidebar($_SESSION['user_role']);
} else {
    echo '<p>Please log in to access the system.</p>';
}
?>