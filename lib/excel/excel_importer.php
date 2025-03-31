<?php
// Require the Composer autoloader
require BASE_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Excel Importer Utility
 * Handles importing data from Excel files using PhpSpreadsheet
 */
class ExcelImporter {
    /**
     * Import users from Excel file
     * @param string $filePath Path to Excel file
     * @return array Result with success status, message and import stats
     */
    public function importUsers($filePath) {
        // Initialize stats
        $stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        // Check if file exists
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'message' => 'File not found: ' . $filePath,
                'stats' => $stats
            ];
        }
        
        try {
            // Load spreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get headers from first row
            $highestColumn = $worksheet->getHighestColumn();
            $headers = [];
            
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $worksheet->getCell($col . '1')->getValue();
                if ($cellValue) {
                    $headers[$col] = strtolower(trim($cellValue));
                }
            }
            
            // Check required headers
            $requiredHeaders = ['name', 'email', 'password', 'role'];
            foreach ($requiredHeaders as $required) {
                if (!in_array($required, $headers)) {
                    return [
                        'success' => false,
                        'message' => "Missing required column: $required",
                        'stats' => $stats
                    ];
                }
            }
            
            // Get column letters for each required field
            $nameCol = array_search('name', $headers);
            $emailCol = array_search('email', $headers);
            $passwordCol = array_search('password', $headers);
            $roleCol = array_search('role', $headers);
            
            // Process rows
            global $pdo; // Get database connection
            
            // Create User model instance
            require_once BASE_PATH . '/models/User.php';
            $userModel = new User($pdo);
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Get highest row
            $highestRow = $worksheet->getHighestRow();
            
            // Process each row starting from row 2 (after header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $stats['total']++;
                
                // Read data
                $name = trim($worksheet->getCell($nameCol . $row)->getValue());
                $email = trim($worksheet->getCell($emailCol . $row)->getValue());
                $password = trim($worksheet->getCell($passwordCol . $row)->getValue());
                $role = strtolower(trim($worksheet->getCell($roleCol . $row)->getValue()));
                
                // Skip empty rows
                if (empty($name) && empty($email)) {
                    continue;
                }
                
                // Validate data
                $isValid = true;
                
                // Check required fields
                if (empty($name)) {
                    $stats['errors'][] = "Row $row: Name is required";
                    $isValid = false;
                }
                
                if (empty($email)) {
                    $stats['errors'][] = "Row $row: Email is required";
                    $isValid = false;
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $stats['errors'][] = "Row $row: Invalid email format - $email";
                    $isValid = false;
                }
                
                if (empty($password)) {
                    $stats['errors'][] = "Row $row: Password is required";
                    $isValid = false;
                }
                
                if (empty($role)) {
                    $stats['errors'][] = "Row $row: Role is required";
                    $isValid = false;
                } elseif (!in_array($role, ['admin', 'teacher', 'student'])) {
                    $stats['errors'][] = "Row $row: Invalid role (must be admin, teacher, or student) - $role";
                    $isValid = false;
                }
                
                // Create user if valid
                if ($isValid) {
                    $userData = [
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'role' => $role
                    ];
                    
                    $result = $userModel->create($userData);
                    if ($result) {
                        $stats['success']++;
                    } else {
                        $stats['failed']++;
                        $stats['errors'][] = "Row $row: Failed to create user $email - Email may already exist";
                    }
                } else {
                    $stats['failed']++;
                }
            }
            
            // Commit or rollback transaction
            if ($stats['success'] > 0) {
                $pdo->commit();
                return [
                    'success' => true,
                    'message' => "Successfully imported {$stats['success']} out of {$stats['total']} users",
                    'stats' => $stats
                ];
            } else {
                // No successful imports, rollback
                $pdo->rollBack();
                return [
                    'success' => false,
                    'message' => "No users were imported successfully. Please check the errors.",
                    'stats' => $stats
                ];
            }
            
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error reading Excel file: ' . $e->getMessage(),
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            // Rollback transaction on error
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            return [
                'success' => false,
                'message' => 'Error processing Excel file: ' . $e->getMessage(),
                'stats' => $stats
            ];
        }
    }
}