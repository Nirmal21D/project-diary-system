<?php

class AdminController {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Gets the total count of users in the system
     * @return int Total number of users
     */
    public function getUserCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting user count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get user count by role
     * @param string $role User role (admin, teacher, student)
     * @return int Number of users with specified role
     */
    public function getUserCountByRole($role) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting user count by role: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total number of projects
     * @return int Total project count
     */
    public function getProjectCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM project_groups");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting project count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get recent users
     * @param int $limit Number of users to retrieve
     * @return array Recent users
     */
    public function getRecentUsers($limit = 5) {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent projects with associated teacher name and student count
     * @param int $limit Number of projects to retrieve
     * @return array Recent projects with details
     */
    public function getRecentProjects($limit = 5) {
        try {
            $query = "
                SELECT 
                    pg.id,
                    pg.name,
                    pg.created_at,
                    u.name AS teacher_name,
                    (SELECT COUNT(*) FROM project_group_members pgm WHERE pgm.project_group_id = pg.id) AS student_count
                FROM 
                    project_groups pg
                LEFT JOIN 
                    users u ON pg.teacher_id = u.id
                ORDER BY 
                    pg.created_at DESC
                LIMIT :limit
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent projects: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all users with optional filtering
     * @param string $role Optional role filter
     * @return array List of users
     */
    public function getAllUsers($role = null) {
        try {
            $query = "SELECT id, name, email, role, created_at FROM users";
            $params = [];
            
            if ($role) {
                $query .= " WHERE role = :role";
                $params[':role'] = $role;
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new user
     * @param array $userData User data (name, email, password, role)
     * @return bool True if successful, false otherwise
     */
    public function createUser($userData) {
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
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }
}

?>