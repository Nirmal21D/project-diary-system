<?php
session_start();
include_once '../../config/database.php';
include_once '../../models/DiaryEntry.php';

$database = new Database();
$db = $database->getConnection();

$diaryEntry = new DiaryEntry($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $diaryEntry->user_id = $_SESSION['user_id'];
    $diaryEntry->group_id = $_POST['group_id'];
    $diaryEntry->content = $_POST['content'];
    $diaryEntry->status = 'submitted';

    if ($diaryEntry->create()) {
        echo "<script>alert('Diary entry submitted successfully!');</script>";
    } else {
        echo "<script>alert('Unable to submit diary entry. Please try again.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diary Entry</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include_once '../../includes/header.php'; ?>
    <?php include_once '../../includes/sidebar.php'; ?>

    <div class="container">
        <h2>Submit Diary Entry</h2>
        <form action="diary_entry.php" method="POST">
            <div class="form-group">
                <label for="group_id">Project Group:</label>
                <select name="group_id" required>
                    <!-- Options should be populated from the database -->
                    <option value="">Select Group</option>
                    <?php
                    // Fetch project groups from the database
                    $query = "SELECT id, name FROM project_groups WHERE teacher_id = :teacher_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':teacher_id', $_SESSION['user_id']);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="content">Diary Entry:</label>
                <textarea name="content" rows="10" required></textarea>
            </div>
            <button type="submit">Submit Entry</button>
        </form>
    </div>

    <div class="container-fluid px-4">
        <h1 class="mt-4">My Diary Entries</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Diary Entries</li>
        </ol>
        
        <!-- Add Diary Entry Button -->
        <div class="mb-4">
            <a href="index.php?page=create_diary_entry" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create New Entry
            </a>
        </div>
        
        <!-- Diary Entries Table -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                All Diary Entries
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>First Day of Work</td>
                            <td>Mobile App Development</td>
                            <td>Jan 16, 2023</td>
                            <td><span class="badge bg-success">Reviewed</span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-primary">View</a>
                                <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                            </td>
                        </tr>
                        <tr>
                            <td>Database Schema Design</td>
                            <td>Database Design</td>
                            <td>Feb 12, 2023</td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-primary">View</a>
                                <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                            </td>
                        </tr>
                        <tr>
                            <td>Initial Wireframes</td>
                            <td>Web Development</td>
                            <td>Dec 10, 2022</td>
                            <td><span class="badge bg-success">Reviewed</span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-primary">View</a>
                                <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Diary Statistics -->
        <div class="row">
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-1"></i>
                        Entry Statistics
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5>3</h5>
                                        <div>Total Entries</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5>2</h5>
                                        <div>Reviewed Entries</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5>1</h5>
                                        <div>Pending Review</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5>3</h5>
                                        <div>Projects Covered</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-check-circle me-1"></i>
                        Recent Feedback
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">First Day of Work</h5>
                                    <small>Jan 17, 2023</small>
                                </div>
                                <p class="mb-1">Good start! Make sure to include more details about the specific tasks you worked on.</p>
                                <small class="text-muted">By: Professor Johnson</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">Initial Wireframes</h5>
                                    <small>Dec 12, 2022</small>
                                </div>
                                <p class="mb-1">Excellent work on documenting your design process. Consider adding sketches next time.</p>
                                <small class="text-muted">By: Professor Johnson</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>