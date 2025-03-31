<?php
// Get student ID from session
$studentId = $_SESSION['user_id'] ?? 0;

// Get projects assigned to the student
$projects = $studentController->getStudentProjects($studentId);

// Calculate statistics
$activeProjects = array_filter($projects, function($p) { return $p['status'] === 'active'; });
$completedProjects = array_filter($projects, function($p) { return $p['status'] === 'completed'; });
$pendingProjects = array_filter($projects, function($p) { return $p['status'] === 'pending'; });
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-2 text-gray-800">My Projects</h1>
            <p class="mb-4">View and manage all projects assigned to you.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Project Status Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-primary">Active Projects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo count($activeProjects); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-success">Completed Projects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo count($completedProjects); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-warning">Pending Projects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo count($pendingProjects); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                <h5>No Projects Assigned Yet</h5>
                <p class="text-muted">You don't have any assigned projects. Your teacher will assign projects to you.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Projects List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">My Projects</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Description</th>
                                
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['name']); ?></td>
                                    <td><?php echo strlen($project['description']) > 50 ? htmlspecialchars(substr($project['description'], 0, 50)) . '...' : htmlspecialchars($project['description']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch ($project['status']) {
                                                case 'active': echo 'primary'; break;
                                                case 'pending': echo 'warning'; break;
                                                case 'completed': echo 'success'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?page=view_project&id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="index.php?page=create_diary_entry&project_id=<?php echo $project['id']; ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus"></i> Add Entry
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>