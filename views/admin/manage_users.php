<?php
// manage_users.php

// Remove session_start() as it's already started in index.php
// session_start();

// Use BASE_PATH for includes instead of relative paths
// require_once('../../config/database.php');

// The database connection ($pdo) and AdminController are already available from index.php
// No need to re-include or re-instantiate

// Get users from the database
$users = $adminController->getAllUsers();

// Filter by role if specified
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';
if (!empty($roleFilter)) {
    $users = $adminController->getAllUsers($roleFilter);
}
?>

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-users me-1"></i> User Management
                </div>
                <div>
                    <a href="index.php?page=add_user" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                    <a href="index.php?page=import_users" class="btn btn-success btn-sm">
                        <i class="fas fa-file-import"></i> Import Users
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Role filter buttons -->
            <div class="mb-3">
                <a href="index.php?page=manage_users" class="btn btn-outline-secondary <?php echo empty($roleFilter) ? 'active' : ''; ?>">All Users</a>
                <a href="index.php?page=manage_users&role=admin" class="btn btn-outline-danger <?php echo $roleFilter === 'admin' ? 'active' : ''; ?>">Admins</a>
                <a href="index.php?page=manage_users&role=teacher" class="btn btn-outline-warning <?php echo $roleFilter === 'teacher' ? 'active' : ''; ?>">Teachers</a>
                <a href="index.php?page=manage_users&role=student" class="btn btn-outline-success <?php echo $roleFilter === 'student' ? 'active' : ''; ?>">Students</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'teacher' ? 'warning' : 'success'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="index.php?page=edit_user&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete <?php echo htmlspecialchars($user['name']); ?>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <a href="index.php?page=delete_user&id=<?php echo $user['id']; ?>" class="btn btn-danger">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>