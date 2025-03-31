<?php
/**
 * User Model
 * Handles user data operations
 */
class User {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get user by ID
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new user
     * @param array $userData User data (name, email, password, role)
     * @return int|bool The new user ID or false if creation failed
     */
    public function create($userData) {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->bindParam(':email', $userData['email']);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                // Email already exists
                return false;
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role, created_at) 
                VALUES (:name, :email, :password, :role, NOW())
            ");
            
            $stmt->bindParam(':name', $userData['name']);
            $stmt->bindParam(':email', $userData['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $userData['role']);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Import users from array data
     * @param array $users Array of user data
     * @return array Result with success status and message
     */
    public function importUsers($users) {
        // Implementation for batch import
        return [
            'success' => false,
            'message' => 'User import functionality not yet implemented'
        ];
    }
}