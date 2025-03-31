<?php
// import_users.php

// Include necessary files - use BASE_PATH for all includes
require_once BASE_PATH . '/lib/excel/excel_importer.php';
require_once BASE_PATH . '/models/User.php';

// Initialize variables
$importResult = null;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    // Validate file type
    $allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel', // .xls
        'text/csv' // .csv
    ];
    
    if (in_array($file['type'], $allowedTypes)) {
        // Process the uploaded file
        $importer = new ExcelImporter();
        $importResult = $importer->importUsers($file['tmp_name']);
    } else {
        $importResult = [
            'success' => false,
            'message' => 'Invalid file type. Please upload an Excel file (.xlsx, .xls) or CSV file.',
            'stats' => ['errors' => []]
        ];
    }
}
?>

<div class="container-fluid px-4">
    <?php if ($importResult): ?>
        <div class="alert alert-<?php echo $importResult['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <h5><i class="fas fa-<?php echo $importResult['success'] ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $importResult['message']; ?></h5>
            <?php if (isset($importResult['stats'])): ?>
                <hr>
                <p>
                    Total rows: <?php echo $importResult['stats']['total']; ?> | 
                    Successful: <?php echo $importResult['stats']['success']; ?> | 
                    Failed: <?php echo $importResult['stats']['failed']; ?>
                </p>
                <?php if (!empty($importResult['stats']['errors'])): ?>
                    <div class="mt-3">
                        <strong>Errors:</strong>
                        <ul>
                            <?php foreach ($importResult['stats']['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Excel File Format Requirements</h4>
        </div>
        <div class="card-body">
            <p>Please create an Excel file with the following columns in the first row:</p>
            
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Column A</th>
                        <th>Column B</th>
                        <th>Column C</th>
                        <th>Column D</th>
                        <th>Column E (Optional)</th>
                    </tr>
                </thead>
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>ID Number</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>John Doe</td>
                        <td>john.doe@example.com</td>
                        <td>password123</td>
                        <td>student</td>
                        <td>ST12345</td>
                    </tr>
                    <tr>
                        <td>Jane Smith</td>
                        <td>jane.smith@example.com</td>
                        <td>password123</td>
                        <td>teacher</td>
                        <td>TC54321</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="alert alert-info mt-3">
                <strong>Important Notes:</strong>
                <ul>
                    <li>The first row must contain the column headers exactly as shown above</li>
                    <li>All fields are required for each user</li>
                    <li>Role must be one of: admin, teacher, student</li>
                    <li>Passwords will be hashed automatically when imported</li>
                    <li>Duplicate emails will be rejected</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Upload Excel File</h4>
        </div>
        <div class="card-body">
            <form action="index.php?page=import_users" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="excel_file">Choose Excel File:</label>
                    <input type="file" class="form-control-file" name="excel_file" id="excel_file" required>
                    <small class="form-text text-muted">Accepted formats: .xlsx, .xls, .csv</small>
                </div>
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-upload"></i> Import Users
                </button>
            </form>
        </div>
    </div>
</div>