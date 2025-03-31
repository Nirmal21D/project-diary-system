<?php
// Initialize settings
$settings = [
    'site_name' => 'Project Diary System',
    'site_description' => 'A system for managing student project diaries',
    'admin_email' => 'admin@example.com',
    'items_per_page' => 10,
    'allow_registration' => 1,
    'maintenance_mode' => 0,
    'smtp_host' => '',
    'smtp_port' => '',
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
    'notification_email' => 'noreply@example.com'
];

// Load current settings from database
try {
    $stmt = $pdo->query("SELECT * FROM system_settings");
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
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
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Prepare statement for inserting/updating settings
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value) 
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE setting_value = :value
        ");
        
        // Update each setting
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8); // Remove 'setting_' prefix
                if (array_key_exists($settingKey, $settings)) {
                    $stmt->execute([
                        ':key' => $settingKey,
                        ':value' => $value
                    ]);
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Update local settings array with new values
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8);
                if (array_key_exists($settingKey, $settings)) {
                    $settings[$settingKey] = $value;
                }
            }
        }
        
        $successMessage = 'Settings updated successfully';
    } catch (Exception $e) {
        // Roll back transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errorMessage = 'Error updating settings: ' . $e->getMessage();
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
            <!-- General Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-cog me-1"></i> General Settings
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="setting_site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="setting_site_name" name="setting_site_name" 
                                value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_site_description" class="form-label">Site Description</label>
                            <textarea class="form-control" id="setting_site_description" name="setting_site_description" rows="2"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_admin_email" class="form-label">Admin Email</label>
                            <input type="email" class="form-control" id="setting_admin_email" name="setting_admin_email" 
                                value="<?php echo htmlspecialchars($settings['admin_email']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_items_per_page" class="form-label">Items Per Page</label>
                            <input type="number" class="form-control" id="setting_items_per_page" name="setting_items_per_page" 
                                value="<?php echo (int)$settings['items_per_page']; ?>" min="5" max="100">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="setting_allow_registration" name="setting_allow_registration" value="1" <?php echo $settings['allow_registration'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="setting_allow_registration">Allow User Registration</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="setting_maintenance_mode" name="setting_maintenance_mode" value="1" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="setting_maintenance_mode">Maintenance Mode</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Email Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-envelope me-1"></i> Email Settings
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="setting_notification_email" class="form-label">Notification From Email</label>
                            <input type="email" class="form-control" id="setting_notification_email" name="setting_notification_email" 
                                value="<?php echo htmlspecialchars($settings['notification_email']); ?>">
                            <small class="form-text text-muted">This email will be used as the sender for system emails.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" id="setting_smtp_host" name="setting_smtp_host" 
                                value="<?php echo htmlspecialchars($settings['smtp_host']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_smtp_port" class="form-label">SMTP Port</label>
                            <input type="text" class="form-control" id="setting_smtp_port" name="setting_smtp_port" 
                                value="<?php echo htmlspecialchars($settings['smtp_port']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" id="setting_smtp_username" name="setting_smtp_username" 
                                value="<?php echo htmlspecialchars($settings['smtp_username']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" id="setting_smtp_password" name="setting_smtp_password" 
                                value="<?php echo htmlspecialchars($settings['smtp_password']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="setting_smtp_encryption" class="form-label">SMTP Encryption</label>
                            <select class="form-select" id="setting_smtp_encryption" name="setting_smtp_encryption">
                                <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="none" <?php echo $settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>None</option>
                            </select>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary" id="test-email">
                                <i class="fas fa-paper-plane"></i> Test Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 text-center mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test email button functionality
    document.getElementById('test-email').addEventListener('click', function() {
        alert('Email test functionality will be implemented in a future update.');
    });
});
</script>