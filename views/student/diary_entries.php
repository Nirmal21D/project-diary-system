<?php
$studentId = $_SESSION['user_id'] ?? 0;

try {
    // Get all diary entries for the student
    $diaryEntries = $studentController->getDiaryEntries($studentId);
} catch (Exception $e) {
    $errorMessage = "Error loading diary entries: " . $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">My Diary Entries</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Diary Entries</li>
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
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="fas fa-table me-1"></i> My Diary Entries</div>
            <a href="index.php?page=create_diary_entry" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Entry
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($diaryEntries) && count($diaryEntries) > 0): ?>
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diaryEntries as $entry): ?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($entry['entry_date'])); ?></td>
                                <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                <td><?php echo htmlspecialchars($entry['project_name']); ?></td>
                                <td>
                                    <?php if ($entry['reviewed']): ?>
                                        <span class="badge bg-success">Reviewed</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?page=view_diary_entry&id=<?php echo $entry['id']; ?>">View</a>
                                    <a href="index.php?page=edit_diary_entry&id=<?php echo $entry['id']; ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    You haven't created any diary entries yet. 
                    <a href="index.php?page=create_diary_entry" class="alert-link">Create your first diary entry</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>