<?php
// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacherId = $_SESSION['user_id'] ?? 0;

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get project details
$project = $teacherController->getProjectDetails($projectId, $teacherId);

// Check if project exists and belongs to this teacher
if (!$project) {
    $errorMessage = "Project not found or you don't have permission to view it.";
}

// Get student information
$students = [];
if ($project) {
    $studentIds = json_decode($project['student_ids'] ?? '[]', true);
    $students = $teacherController->getStudentsInfo($studentIds);
    
    // Get recent diary entries for this project
    $recentEntries = $teacherController->getProjectDiaryEntries($projectId, 5);
}
?>

<div class="container-fluid px-4">
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <?php echo $errorMessage; ?>
            <a href="index.php?page=teacher_projects" class="btn btn-primary float-end">
                <i class="fas fa-arrow-left"></i> Back to Projects
            </a>
        </div>
    <?php else: ?>
        <!-- Project Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-project-diagram me-1"></i> 
                            <?php echo htmlspecialchars($project['name']); ?>
                        </h5>
                        <div>
                            <a href="index.php?page=edit_project&id=<?php echo $projectId; ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i> Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=manage_project_students&id=<?php echo $projectId; ?>">
                                            <i class="fas fa-users"></i> Manage Students
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=project_diary_entries&id=<?php echo $projectId; ?>">
                                            <i class="fas fa-book"></i> All Diary Entries
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=teacher_projects&change_status=1&project_id=<?php echo $projectId; ?>&status=active">
                                            <i class="fas fa-check"></i> Mark as Active
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=teacher_projects&change_status=1&project_id=<?php echo $projectId; ?>&status=completed">
                                            <i class="fas fa-check-double"></i> Mark as Completed
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=teacher_projects&change_status=1&project_id=<?php echo $projectId; ?>&status=archived">
                                            <i class="fas fa-archive"></i> Archive Project
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="fw-bold">Description</h6>
                                <div class="border p-3 bg-light mb-3" style="min-height: 100px;">
                                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>
                                            <strong>Start Date:</strong> 
                                            <?php echo date('F d, Y', strtotime($project['start_date'])); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <strong>End Date:</strong> 
                                            <?php echo date('F d, Y', strtotime($project['end_date'])); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <p>
                                    <strong>Created:</strong> 
                                    <?php echo date('F d, Y', strtotime($project['created_at'])); ?>
                                </p>
                            </div>
                            
                            <div class="col-md-4">
                                <?php 
                                    $statusClass = '';
                                    $status = $project['status'] ?? 'pending';
                                    switch ($status) {
                                        case 'active':
                                            $statusClass = 'success';
                                            break;
                                        case 'pending':
                                            $statusClass = 'warning';
                                            break;
                                        case 'completed':
                                            $statusClass = 'info';
                                            break;
                                        case 'archived':
                                            $statusClass = 'secondary';
                                            break;
                                    }
                                ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-<?php echo $statusClass; ?> text-white">
                                        Project Status
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="text-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </h3>
                                    </div>
                                </div>
                                
                                <!-- Progress -->
                                <?php 
                                    // Calculate days elapsed and remaining
                                    $startDate = new DateTime($project['start_date']);
                                    $endDate = new DateTime($project['end_date']);
                                    $currentDate = new DateTime();
                                    
                                    $totalDays = $startDate->diff($endDate)->days;
                                    $daysElapsed = $startDate->diff($currentDate)->days;
                                    
                                    // Ensure percentage is between 0-100
                                    $progressPercent = $totalDays > 0 ? min(100, max(0, ($daysElapsed / $totalDays) * 100)) : 0;
                                ?>
                                
                                <div class="card">
                                    <div class="card-header">
                                        Project Timeline
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-2">
                                            <div class="progress-bar progress-bar-striped" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $progressPercent; ?>%" 
                                                 aria-valuenow="<?php echo $progressPercent; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?php echo round($progressPercent); ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php 
                                                if ($currentDate < $startDate) {
                                                    echo "Project starts in " . $currentDate->diff($startDate)->days . " days";
                                                } else if ($currentDate > $endDate) {
                                                    echo "Project ended " . $currentDate->diff($endDate)->days . " days ago";
                                                } else {
                                                    echo $endDate->diff($currentDate)->days . " days remaining";
                                                }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Students and Diary Entries -->
        <div class="row">
            <!-- Enrolled Students -->
            <div class="col-xl-5">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-users me-1"></i> Enrolled Students</div>
                        <a href="index.php?page=manage_project_students&id=<?php echo $projectId; ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Manage
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($students)): ?>
                            <div class="alert alert-warning">
                                No students are enrolled in this project.
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($students as $student): ?>
                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-graduate me-2"></i>
                                            <?php echo htmlspecialchars($student['name']); ?>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($student['email']); ?></small>
                                        </div>
                                        <div>
                                            <a href="index.php?page=student_diary_entries&student_id=<?php echo $student['id']; ?>&project_id=<?php echo $projectId; ?>"
                                               class="btn btn-sm btn-info" title="View Student Entries">
                                                <i class="fas fa-book"></i>
                                            </a>
                                            <a href="index.php?page=message_student&id=<?php echo $student['id']; ?>"
                                               class="btn btn-sm btn-success" title="Message Student">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Diary Entries -->
            <div class="col-xl-7">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-book me-1"></i> Recent Diary Entries</div>
                        <a href="index.php?page=project_diary_entries&id=<?php echo $projectId; ?>"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> View All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentEntries)): ?>
                            <div class="alert alert-info">
                                No diary entries have been submitted for this project yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Student</th>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEntries as $entry): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($entry['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                                <td>
                                                    <?php if ($entry['reviewed']): ?>
                                                        <span class="badge bg-success">Reviewed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
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
        </div>
    <?php endif; ?>
</div>