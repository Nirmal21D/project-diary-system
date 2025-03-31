<?php
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacherId = $_SESSION['user_id'] ?? 0;

try {
    // Verify permission to view this student
    $hasPermission = $teacherController->canViewStudent($studentId, $teacherId);
    
    if (!$hasPermission) {
        $_SESSION['error_message'] = "You don't have permission to view this student.";
        echo "<script>window.location.href = 'index.php?page=teacher_students';</script>";
        exit;
    }
    
    // Get student details
    $student = $teacherController->getStudentWithDetails($studentId);
    
    // Get projects assigned to this student
    $studentProjects = $teacherController->getStudentProjects($studentId);
    
    // Get recent diary entries
    $recentEntries = $teacherController->getStudentDiaryEntries($studentId, 5);
    
} catch (Exception $e) {
    $errorMessage = "Error loading student data: " . $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Student Profile</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=teacher_students">Students</a></li>
        <li class="breadcrumb-item active">Student Profile</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($student)): ?>
    <div class="row">
        <!-- Student Profile Card -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i> Student Information
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                        </div>
                        <h3 class="mb-1"><?php echo htmlspecialchars($student['name']); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($student['email']); ?></p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Projects
                            <span class="badge bg-primary rounded-pill"><?php echo count($studentProjects); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Diary Entries
                            <span class="badge bg-info rounded-pill"><?php echo $student['total_entries'] ?? 0; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Pending Reviews
                            <span class="badge bg-warning rounded-pill"><?php echo $student['pending_reviews'] ?? 0; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Last Active
                            <span>
                                <?php 
                                    echo isset($student['last_activity']) && $student['last_activity'] 
                                        ? date('M j, Y', strtotime($student['last_activity'])) 
                                        : 'Never'; 
                                ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Joined
                            <span><?php echo date('M j, Y', strtotime($student['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="index.php?page=student_diary_entries&student=<?php echo $student['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-book me-1"></i> View All Diary Entries
                        </a>
                        <a href="mailto:<?php echo $student['email']; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-envelope me-1"></i> Send Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Overview -->
        <div class="col-xl-8">
            <div class="row">
                <!-- Project List -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-project-diagram me-1"></i> Assigned Projects
                        </div>
                        <div class="card-body">
                            <?php if (empty($studentProjects)): ?>
                                <div class="alert alert-info">
                                    This student is not assigned to any projects yet.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Project Name</th>
                                                <th>Start Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($studentProjects as $project): ?>
                                                <tr>
                                                    <td>
                                                        <a href="index.php?page=view_project_group&id=<?php echo $project['id']; ?>">
                                                            <?php echo htmlspecialchars($project['name']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($project['joined_date'])); ?></td>
                                                    <td>
                                                        <?php if ($project['status'] === 'active'): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php elseif ($project['status'] === 'completed'): ?>
                                                            <span class="badge bg-info">Completed</span>
                                                        <?php elseif ($project['status'] === 'archived'): ?>
                                                            <span class="badge bg-secondary">Archived</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning"><?php echo ucfirst($project['status']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="index.php?page=student_diary_entries&student=<?php echo $student['id']; ?>&project=<?php echo $project['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-book"></i> View Entries
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Entries -->
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-clock me-1"></i> Recent Diary Entries
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentEntries)): ?>
                                <div class="alert alert-info">
                                    This student hasn't submitted any diary entries yet.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Project</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentEntries as $entry): ?>
                                                <tr>
                                                    <td><?php echo date('M j, Y', strtotime($entry['entry_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($entry['project_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                                    <td>
                                                        <?php if ($entry['reviewed']): ?>
                                                            <span class="badge bg-success">Reviewed</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="index.php?page=student_diary_entries&student=<?php echo $student['id']; ?>" class="btn btn-outline-primary">
                                        View All Entries
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Student not found or you don't have permission to view this student's profile.
            <a href="index.php?page=teacher_students" class="alert-link">Return to students list</a>
        </div>
    <?php endif; ?>
</div>