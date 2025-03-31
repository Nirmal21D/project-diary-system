<?php
// Initialize settings and fetch from database
$institutionalInfo = [
    'vision' => '',
    'mission' => '',
    'core_values' => '',
    'guidelines' => '',
    'about_us' => '',
    'contact_info' => ''
];

// Load current info from database
try {
    $stmt = $pdo->query("SELECT * FROM institutional_info");
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $institutionalInfo[$row['info_key']] = $row['info_value'];
        }
    }
} catch (PDOException $e) {
    // Table may not exist yet
}

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if table exists, create if not
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS institutional_info (
                id INT AUTO_INCREMENT PRIMARY KEY,
                info_key VARCHAR(50) NOT NULL UNIQUE,
                info_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                updated_by INT,
                FOREIGN KEY (updated_by) REFERENCES users(id)
            )
        ");
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Prepare statement for inserting/updating info
        $stmt = $pdo->prepare("
            INSERT INTO institutional_info (info_key, info_value, updated_by) 
            VALUES (:key, :value, :user_id)
            ON DUPLICATE KEY UPDATE info_value = :value, updated_by = :user_id
        ");
        
        // Update each info field
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'info_') === 0) {
                $infoKey = substr($key, 5); // Remove 'info_' prefix
                if (array_key_exists($infoKey, $institutionalInfo)) {
                    $stmt->execute([
                        ':key' => $infoKey,
                        ':value' => $value,
                        ':user_id' => $_SESSION['user_id']
                    ]);
                    // Update local array with new values
                    $institutionalInfo[$infoKey] = $value;
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        $successMessage = 'Institutional information updated successfully';
    } catch (Exception $e) {
        // Roll back transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errorMessage = 'Error updating information: ' . $e->getMessage();
    }
}
?>

<div class="container-fluid px-4">
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-university me-1"></i> Vision & Mission
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="info_vision" class="form-label fw-bold">Vision</label>
                            <textarea class="form-control" id="info_vision" name="info_vision" rows="4"><?php echo htmlspecialchars($institutionalInfo['vision']); ?></textarea>
                            <small class="text-muted">The institution's vision statement describes aspirations and the future state.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="info_mission" class="form-label fw-bold">Mission</label>
                            <textarea class="form-control" id="info_mission" name="info_mission" rows="4"><?php echo htmlspecialchars($institutionalInfo['mission']); ?></textarea>
                            <small class="text-muted">The mission statement defines the institution's purpose and primary objectives.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="info_core_values" class="form-label fw-bold">Core Values</label>
                            <textarea class="form-control" id="info_core_values" name="info_core_values" rows="4"><?php echo htmlspecialchars($institutionalInfo['core_values']); ?></textarea>
                            <small class="text-muted">Core values that guide the institution's operations and decisions.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-book me-1"></i> Guidelines & Additional Information
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="info_guidelines" class="form-label fw-bold">Project Guidelines</label>
                            <textarea class="form-control" id="info_guidelines" name="info_guidelines" rows="6"><?php echo htmlspecialchars($institutionalInfo['guidelines']); ?></textarea>
                            <small class="text-muted">Guidelines for students and teachers working on projects.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="info_about_us" class="form-label fw-bold">About Us</label>
                            <textarea class="form-control" id="info_about_us" name="info_about_us" rows="4"><?php echo htmlspecialchars($institutionalInfo['about_us']); ?></textarea>
                            <small class="text-muted">Brief description of the institution for the About Us page.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="info_contact_info" class="form-label fw-bold">Contact Information</label>
                            <textarea class="form-control" id="info_contact_info" name="info_contact_info" rows="4"><?php echo htmlspecialchars($institutionalInfo['contact_info']); ?></textarea>
                            <small class="text-muted">Contact details including address, phone number, email, etc.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 text-center mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Information
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // You can add rich text editor initialization here 
    // For example, TinyMCE, CKEditor, etc.
    
    // Example (commented out - you would need to include the library):
    /*
    tinymce.init({
        selector: 'textarea',
        height: 300,
        plugins: 'link lists image code table',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | code'
    });
    */
});
</script>