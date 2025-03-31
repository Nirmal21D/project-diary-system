<div class="list-group list-group-flush">
    <a href="index.php?page=dashboard" class="list-group-item list-group-item-action <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    
    <a href="index.php?page=manage_users" class="list-group-item list-group-item-action <?php echo $page === 'manage_users' || $page === 'edit_user' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Manage Users
    </a>
    
    <a href="index.php?page=add_user" class="list-group-item list-group-item-action <?php echo $page === 'add_user' ? 'active' : ''; ?>">
        <i class="fas fa-user-plus"></i> Add New User
    </a>
    
    <a href="index.php?page=import_users" class="list-group-item list-group-item-action <?php echo $page === 'import_users' ? 'active' : ''; ?>">
        <i class="fas fa-file-import"></i> Import Users
    </a>
    
    <a href="index.php?page=manage_projects" class="list-group-item list-group-item-action <?php echo $page === 'manage_projects' || $page === 'view_project' ? 'active' : ''; ?>">
        <i class="fas fa-project-diagram"></i> Manage Projects
    </a>
    
    <a href="index.php?page=system_settings" class="list-group-item list-group-item-action <?php echo $page === 'system_settings' ? 'active' : ''; ?>">
        <i class="fas fa-cogs"></i> System Settings
    </a>
    
    <a href="index.php?page=logout" class="list-group-item list-group-item-action text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>