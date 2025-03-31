<?php
// Get teacher ID from session
$teacherId = $_SESSION['user_id'] ?? 0;

// Create TeacherController if not already available
if (!isset($teacherController)) {
    require_once BASE_PATH . '/controllers/TeacherController.php';
    $teacherController = new TeacherController($pdo);
}

// Get filter parameters
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get entries based on filters
$diaryEntries = $teacherController->getFilteredDiaryEntries($teacherId, $projectId, $studentId, $status);

// Get projects for filter
$projects = $teacherController->getTeacherProjects($teacherId);
?>

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-book me-1"></i> Diary Entries
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="get" action="index.php" class="row g-3">
                        <input type="hidden" name="page" value="teacher_diary_entries">
                        
                        <div class="col-md-3">
                            <label for="project_id" class="form-label">Filter by Project</label>
                            <select class="form-select" id="project_id" name="project_id">
                                <option value="">All Projects</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>" <?php echo $projectId == $project['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="status" class="form-label">Filter by Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="index.php?page=teacher_diary_entries" class="btn btn-secondary ms-2">
                                <i class="fas fa-sync"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (empty($diaryEntries)): ?>
                <div class="alert alert-info">
                    <p>No diary entries found matching your criteria.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="diaryEntriesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Project</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diaryEntries as $entry): ?>
                                <tr>
                                    <td><?php echo $entry['id']; ?></td>
                                    <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['project_name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($entry['created_at'])); ?></td>
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
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    if (document.getElementById('diaryEntriesTable')) {
        $('#diaryEntriesTable').DataTable({
            order: [[4, 'desc']], // Sort by date (column 4) descending
            responsive: true
        });
    }
});
</script>