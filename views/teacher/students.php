<?php
// Get teacher ID from session
$teacherId = $_SESSION['user_id'] ?? 0;

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get all students assigned to this teacher's projects
$students = $teacherController->getTeacherStudents($teacherId);

// Add method to TeacherController
// This will be implemented later
?>

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-graduate me-1"></i> My Students
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    <p>No students are currently assigned to your projects.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="studentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Projects</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td>
                                        <?php foreach ($student['projects'] as $project): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($project['name']); ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?php echo $student['last_activity'] ? date('M d, Y H:i', strtotime($student['last_activity'])) : 'No activity'; ?></td>
                                    <td>
                                        <a href="index.php?page=student_details&id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?page=student_diary_entries&id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="index.php?page=message_student&id=<?php echo $student['id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-envelope"></i>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    if (document.getElementById('studentsTable')) {
        $('#studentsTable').DataTable({
            responsive: true
        });
    }
});
</script>