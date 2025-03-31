<?php
// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $message = 'All fields are required';
        $messageType = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format';
        $messageType = 'danger';
    } else {
        // Create user
        $result = $adminController->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
        
        if ($result) {
            $message = 'User created successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to create user. Email may already be in use.';
            $messageType = 'danger';
        }
    }
}
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-plus me-1"></i> Add New User
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="role" class="form-label">User Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">-- Select Role --</option>
                                <option value="admin">Administrator</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="index.php?page=manage_users" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i> User Role Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="text-danger">Administrator</h5>
                        <p>Full system access. Can manage users, projects, and system settings.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="text-warning">Teacher</h5>
                        <p>Can create and manage projects, review student diary entries, and provide feedback.</p>
                    </div>
                    
                    <div>
                        <h5 class="text-success">Student</h5>
                        <p>Can join projects, submit diary entries, and view teacher feedback.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>