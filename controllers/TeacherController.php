<?php
/**
 * Teacher Controller
 * Handles all teacher-related operations
 */
class TeacherController {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get teacher details
     * @param int $teacherId Teacher ID
     * @return array|false Teacher information or false if not found
     */
    public function getTeacherDetails($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email, role
                FROM users
                WHERE id = :id AND role = 'teacher'
            ");
            $stmt->bindParam(':id', $teacherId);
            $stmt->execute();
            
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$teacher) {
                error_log("Teacher with ID $teacherId not found");
                return false;
            }
            
            return $teacher;
        } catch (PDOException $e) {
            error_log("Error getting teacher details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get projects managed by a teacher
     * @param int $teacherId Teacher ID
     * @return array List of projects
     */
    public function getTeacherProjects($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM project_groups 
                WHERE teacher_id = :teacher_id
                ORDER BY created_at DESC
            ");
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting teacher projects: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get count of students under a teacher
     * @param int $teacherId Teacher ID
     * @return int Student count
     */
    public function getStudentCount($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT pgm.user_id) as student_count
                FROM project_group_members pgm
                JOIN project_groups pg ON pgm.project_group_id = pg.id
                WHERE pg.teacher_id = :teacher_id
            ");
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting student count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get count of diary entries for a teacher's projects
     * @param int $teacherId Teacher ID
     * @return int Diary entry count
     */
    public function getDiaryEntryCount($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as entry_count
                FROM diary_entries de
                JOIN project_group_members pgm ON de.user_id = pgm.user_id
                JOIN project_groups pg ON pgm.project_group_id = pg.id
                WHERE pg.teacher_id = :teacher_id
            ");
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting diary entry count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get recent diary entries for a teacher's projects
     * @param int $teacherId Teacher ID
     * @param int $limit Number of entries to retrieve
     * @return array Recent diary entries
     */
    public function getRecentDiaryEntries($teacherId, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    de.id,
                    de.title,
                    de.content,
                    de.created_at,
                    de.reviewed,
                    u.name as student_name,
                    pg.name as project_name
                FROM diary_entries de
                JOIN users u ON de.user_id = u.id
                JOIN project_group_members pgm ON de.user_id = pgm.user_id
                JOIN project_groups pg ON pgm.project_group_id = pg.id
                WHERE pg.teacher_id = :teacher_id
                ORDER BY de.created_at DESC
                LIMIT :limit
            ");
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent diary entries: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pending reviews for a teacher
     * @param int $teacherId Teacher ID
     * @return array Pending reviews
     */
    public function getPendingReviews($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    de.id,
                    de.title,
                    de.content,
                    de.created_at,
                    u.name as student_name,
                    pg.name as project_name
                FROM diary_entries de
                JOIN users u ON de.user_id = u.id
                JOIN project_group_members pgm ON de.user_id = pgm.user_id
                JOIN project_groups pg ON pgm.project_group_id = pg.id
                WHERE pg.teacher_id = :teacher_id
                AND (de.reviewed = 0 OR de.reviewed IS NULL)
                ORDER BY de.created_at ASC
            ");
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting pending reviews: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get notifications for a teacher
     * @param int $teacherId Teacher ID
     * @param int $limit Number of notifications to retrieve
     * @return array Notifications
     */
    public function getTeacherNotifications($teacherId, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            $stmt->bindParam(':user_id', $teacherId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting teacher notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get teacher's students with their projects
     * @param int $teacherId Teacher ID
     * @return array Students with projects
     */
    public function getTeacherStudents($teacherId) {
        try {
            // Get all students in teacher's projects
            $stmt = $this->db->prepare("
                SELECT DISTINCT u.id, u.name, u.email, u.last_login
                FROM users u
                JOIN project_group_members pgm ON u.id = pgm.user_id
                JOIN project_groups pg ON pgm.project_group_id = pg.id
                WHERE pg.teacher_id = :teacher_id AND u.role = 'student'
                ORDER BY u.name
            ");
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // For each student, get their projects and last activity
            foreach ($students as &$student) {
                // Get student's projects
                $stmt = $this->db->prepare("
                    SELECT pg.id, pg.name
                    FROM project_groups pg
                    JOIN project_group_members pgm ON pg.id = pgm.project_group_id
                    WHERE pgm.user_id = :student_id AND pg.teacher_id = :teacher_id
                    ORDER BY pg.name
                ");
                $stmt->bindParam(':student_id', $student['id']);
                $stmt->bindParam(':teacher_id', $teacherId);
                $stmt->execute();
                $student['projects'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get student's last diary entry timestamp
                $stmt = $this->db->prepare("
                    SELECT MAX(created_at) as last_activity
                    FROM diary_entries
                    WHERE user_id = :student_id
                ");
                $stmt->bindParam(':student_id', $student['id']);
                $stmt->execute();
                $lastActivity = $stmt->fetch(PDO::FETCH_ASSOC);
                $student['last_activity'] = $lastActivity['last_activity'] ?? $student['last_login'];
            }
            
            return $students;
        } catch (PDOException $e) {
            error_log("Error getting teacher students: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get filtered diary entries for a teacher
     * @param int $teacherId Teacher ID
     * @param int $projectId Optional project ID filter
     * @param int $studentId Optional student ID filter
     * @param string $status Optional status filter (reviewed, pending)
     * @return array Filtered diary entries
     */
    public function getFilteredDiaryEntries($teacherId, $projectId = 0, $studentId = 0, $status = '') {
        try {
            $sql = "
                SELECT 
                    de.id,
                    de.title,
                    de.content,
                    de.created_at,
                    de.reviewed,
                    u.id as student_id,
                    u.name as student_name,
                    pg.id as project_id,
                    pg.name as project_name
                FROM diary_entries de
                JOIN users u ON de.user_id = u.id
                JOIN project_group_members pgm ON de.user_id = pgm.user_id
                JOIN project_groups pg ON pgm.project_group_id = pg.id
                WHERE pg.teacher_id = :teacher_id
            ";
            
            $params = [':teacher_id' => $teacherId];
            
            if ($projectId > 0) {
                $sql .= " AND pg.id = :project_id";
                $params[':project_id'] = $projectId;
            }
            
            if ($studentId > 0) {
                $sql .= " AND u.id = :student_id";
                $params[':student_id'] = $studentId;
            }
            
            if ($status === 'reviewed') {
                $sql .= " AND de.reviewed = 1";
            } else if ($status === 'pending') {
                $sql .= " AND (de.reviewed = 0 OR de.reviewed IS NULL)";
            }
            
            $sql .= " ORDER BY de.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting filtered diary entries: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update project status
     * @param int $projectId Project ID
     * @param string $status New status
     * @param int $teacherId Teacher ID for verification
     * @return bool Success status
     */
    public function updateProjectStatus($projectId, $status, $teacherId) {
        try {
            if (!$this->tableExists('projects')) {
                return false;
            }
            
            // Verify project belongs to teacher
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM projects
                WHERE id = :project_id AND teacher_id = :teacher_id
            ");
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            
            if ($stmt->fetchColumn() == 0) {
                return false; // Project doesn't belong to this teacher
            }
            
            // Update status
            $allowedStatuses = ['pending', 'active', 'completed', 'archived'];
            if (!in_array($status, $allowedStatuses)) {
                return false;
            }
            
            $stmt = $this->db->prepare("
                UPDATE projects
                SET status = :status, updated_at = NOW()
                WHERE id = :project_id
            ");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':project_id', $projectId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating project status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new project
     * @param array $projectData Project data
     * @return int|bool New project ID or false if failed
     */
    public function createProject($projectData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO project_groups (
                    name, description, start_date, end_date, 
                    status, teacher_id, created_at, updated_at
                ) VALUES (
                    :name, :description, :start_date, :end_date,
                    :status, :teacher_id, NOW(), NOW()
                )
            ");
            
            $stmt->bindParam(':name', $projectData['name']);
            $stmt->bindParam(':description', $projectData['description']);
            $stmt->bindParam(':start_date', $projectData['start_date']);
            $stmt->bindParam(':end_date', $projectData['end_date']);
            $stmt->bindParam(':status', $projectData['status']);
            $stmt->bindParam(':teacher_id', $projectData['teacher_id']);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating project: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update an existing project
     * @param array $projectData Project data
     * @param array $studentIds Array of student IDs to assign
     * @return bool|string Success status or error message
     */
    public function updateProject($projectData, $studentIds) {
        try {
            // First verify ownership
            $stmt = $this->db->prepare("
                SELECT teacher_id FROM projects
                WHERE id = :project_id
            ");
            $stmt->bindParam(':project_id', $projectData['id']);
            $stmt->execute();
            
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$project || $project['teacher_id'] != $projectData['teacher_id']) {
                return "You don't have permission to update this project.";
            }
            
            // Update project data
            $stmt = $this->db->prepare("
                UPDATE projects
                SET name = :name,
                    description = :description,
                    start_date = :start_date,
                    end_date = :end_date,
                    status = :status,
                    student_ids = :student_ids,
                    updated_at = NOW()
                WHERE id = :project_id
            ");
            
            // Serialize student IDs
            $serializedStudentIds = json_encode($studentIds);
            
            $stmt->bindParam(':name', $projectData['name']);
            $stmt->bindParam(':description', $projectData['description']);
            $stmt->bindParam(':start_date', $projectData['start_date']);
            $stmt->bindParam(':end_date', $projectData['end_date']);
            $stmt->bindParam(':status', $projectData['status']);
            $stmt->bindParam(':student_ids', $serializedStudentIds);
            $stmt->bindParam(':project_id', $projectData['id']);
            
            $stmt->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error updating project: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("Exception updating project: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }
    
    /**
     * Get project student count
     * @param int $projectId Project ID
     * @return int Number of students in project
     */
    public function getProjectStudentCount($projectId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM project_group_members
                WHERE project_group_id = :project_id
            ");
            $stmt->bindParam(':project_id', $projectId);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting project student count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get all students available for assignment
     * @return array List of students
     */
    public function getAllStudents() {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email
                FROM users
                WHERE role = 'student'
                ORDER BY name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all students: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create a project and assign students to it
     * @param array $projectData Project data
     * @param array $studentIds Array of student IDs to assign
     * @return bool Success status
     */
    public function createProjectWithStudents($projectData, $studentIds) {
        try {
            $this->db->beginTransaction();
            
            // First, ensure tables exist
            $this->ensureTablesExist();
            
            // Create the project
            $stmt = $this->db->prepare("
                INSERT INTO project_groups (
                    name, description, start_date, end_date, 
                    status, teacher_id, created_at, updated_at
                ) VALUES (
                    :name, :description, :start_date, :end_date,
                    :status, :teacher_id, NOW(), NOW()
                )
            ");
            
            $stmt->bindParam(':name', $projectData['name']);
            $stmt->bindParam(':description', $projectData['description']);
            $stmt->bindParam(':start_date', $projectData['start_date']);
            $stmt->bindParam(':end_date', $projectData['end_date']);
            $stmt->bindParam(':status', $projectData['status']);
            $stmt->bindParam(':teacher_id', $projectData['teacher_id']);
            
            $stmt->execute();
            $projectId = $this->db->lastInsertId();
            
            // Next, assign students to the project
            $stmt = $this->db->prepare("
                INSERT INTO project_group_members (
                    project_group_id, user_id, joined_at
                ) VALUES (
                    :project_id, :student_id, NOW()
                )
            ");
            
            foreach ($studentIds as $studentId) {
                $stmt->bindParam(':project_id', $projectId);
                $stmt->bindParam(':student_id', $studentId);
                $stmt->execute();
            }
            
            // Create notification for each student (if notifications table exists)
            if ($this->tableExists('notifications')) {
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (
                        user_id, title, message, link, created_at, is_read
                    ) VALUES (
                        :user_id, :title, :message, :link, NOW(), 0
                    )
                ");
                
                $title = "New Project Assignment";
                $message = "You have been assigned to a new project: " . $projectData['name'];
                $link = "index.php?page=view_project&id=" . $projectId;
                
                foreach ($studentIds as $studentId) {
                    $stmt->bindParam(':user_id', $studentId);
                    $stmt->bindParam(':title', $title);
                    $stmt->bindParam(':message', $message);
                    $stmt->bindParam(':link', $link);
                    $stmt->execute();
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error creating project with students: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a project and assign students to it with detailed debug info
     * @param array $projectData Project data
     * @param array $studentIds Array of student IDs to assign
     * @return array Result with success flag and any error messages
     */
    public function createProjectWithStudentsDebug($projectData, $studentIds) {
        try {
            // Check database connection
            if (!$this->db) {
                return ['success' => false, 'error' => 'Database connection not available'];
            }
            
            // Verify all required project data exists
            $requiredFields = ['name', 'description', 'start_date', 'end_date', 'teacher_id'];
            foreach ($requiredFields as $field) {
                if (!isset($projectData[$field]) || empty($projectData[$field])) {
                    return ['success' => false, 'error' => "Missing required field: {$field}"];
                }
            }
            
            // Check if teacher exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'teacher'");
            $stmt->execute([$projectData['teacher_id']]);
            if ($stmt->fetchColumn() == 0) {
                return ['success' => false, 'error' => "Teacher with ID {$projectData['teacher_id']} not found"];
            }
            
            // Check if students exist
            if (empty($studentIds)) {
                return ['success' => false, 'error' => 'No students selected'];
            }
            
            // Verify selected students exist
            $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id IN ({$placeholders}) AND role = 'student'");
            $stmt->execute($studentIds);
            $foundStudents = $stmt->fetchColumn();
            
            if ($foundStudents != count($studentIds)) {
                return ['success' => false, 'error' => "Some selected students don't exist in the database"];
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Ensure tables exist
            $this->ensureTablesExist();
            
            // Create the project
            $sql = "INSERT INTO project_groups (
                    name, description, start_date, end_date, 
                    status, teacher_id, created_at, updated_at
                ) VALUES (
                    :name, :description, :start_date, :end_date,
                    :status, :teacher_id, NOW(), NOW()
                )";
                
            // Debug SQL
            $debugSql = str_replace(':name', "'".$projectData['name']."'", $sql);
            $debugSql = str_replace(':description', "'".$projectData['description']."'", $debugSql);
            $debugSql = str_replace(':start_date', "'".$projectData['start_date']."'", $debugSql);
            $debugSql = str_replace(':end_date', "'".$projectData['end_date']."'", $debugSql);
            $debugSql = str_replace(':status', "'".$projectData['status']."'", $debugSql);
            $debugSql = str_replace(':teacher_id', $projectData['teacher_id'], $debugSql);
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $projectData['name']);
            $stmt->bindParam(':description', $projectData['description']);
            $stmt->bindParam(':start_date', $projectData['start_date']);
            $stmt->bindParam(':end_date', $projectData['end_date']);
            $stmt->bindParam(':status', $projectData['status']);
            $stmt->bindParam(':teacher_id', $projectData['teacher_id']);
            
            $stmt->execute();
            $projectId = $this->db->lastInsertId();
            
            if (!$projectId) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Failed to get project ID after insert'];
            }
            
            // Next, assign students to the project
            $stmt = $this->db->prepare("
                INSERT INTO project_group_members (
                    project_group_id, user_id, joined_at
                ) VALUES (
                    :project_id, :student_id, NOW()
                )
            ");
            
            foreach ($studentIds as $studentId) {
                $stmt->bindParam(':project_id', $projectId);
                $stmt->bindParam(':student_id', $studentId);
                $stmt->execute();
            }
            
            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error creating project with students: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create a project with students in a single table
     * @param array $projectData Project data
     * @param array $studentIds Array of student IDs to assign
     * @return bool|string Success status or error message
     */
    public function createProjectWithStudentsSingleTable($projectData, $studentIds) {
        try {
            // First get teacher info to include in project
            $teacherId = $projectData['teacher_id'];
            $teacher = $this->getTeacherDetails($teacherId);
            
            if (!$teacher) {
                throw new Exception("Could not retrieve teacher information. Please check if your user account has teacher role.");
            }
            
            // Include teacher name in project data
            $teacherInfo = [
                'id' => $teacher['id'],
                'name' => $teacher['name'],
                'email' => $teacher['email']
            ];
            
            $teacherInfoJson = json_encode($teacherInfo);
            
            // Create projects table if it doesn't exist
            if (!$this->tableExists('projects')) {
                $this->db->exec("
                    CREATE TABLE projects (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        description TEXT,
                        start_date DATE,
                        end_date DATE,
                        status VARCHAR(50) DEFAULT 'pending',
                        teacher_id INT NOT NULL,
                        teacher_info TEXT,
                        student_ids TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
            } else {
                // Check if teacher_info column exists, add it if it doesn't
                $columnsExist = $this->db->query("SHOW COLUMNS FROM projects LIKE 'teacher_info'");
                
                if ($columnsExist->rowCount() === 0) {
                    $this->db->exec("ALTER TABLE projects ADD COLUMN teacher_info TEXT AFTER teacher_id");
                }
            }
            
            // Serialize student IDs as JSON
            $serializedStudentIds = json_encode($studentIds);
            
            // Insert project with student IDs and teacher info in single table
            $stmt = $this->db->prepare("
                INSERT INTO projects (
                    name, description, start_date, end_date, 
                    status, teacher_id, teacher_info, student_ids, created_at, updated_at
                ) VALUES (
                    :name, :description, :start_date, :end_date,
                    :status, :teacher_id, :teacher_info, :student_ids, NOW(), NOW()
                )
            ");
            
            $stmt->bindParam(':name', $projectData['name']);
            $stmt->bindParam(':description', $projectData['description']);
            $stmt->bindParam(':start_date', $projectData['start_date']);
            $stmt->bindParam(':end_date', $projectData['end_date']);
            $stmt->bindParam(':status', $projectData['status']);
            $stmt->bindParam(':teacher_id', $projectData['teacher_id']);
            $stmt->bindParam(':teacher_info', $teacherInfoJson);
            $stmt->bindParam(':student_ids', $serializedStudentIds);
            
            $stmt->execute();
            $projectId = $this->db->lastInsertId();
            
            return true;
        } catch (PDOException $e) {
            // Return the actual error message for debugging
            return "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
    /**
     * Get teacher information
     * @param int $teacherId Teacher ID
     * @return array|false Teacher information or false if not found
     */
    private function getTeacherInfo($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email, department, position 
                FROM users 
                WHERE id = :teacher_id AND role = 'teacher'
            ");
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If department doesn't exist in table, use default
            if (!isset($teacher['department']) || empty($teacher['department'])) {
                $teacher['department'] = 'Not Specified';
            }
            
            return $teacher ?: [
                'name' => 'Unknown Teacher',
                'email' => 'unknown@example.com',
                'department' => 'Not Specified'
            ];
        } catch (PDOException $e) {
            error_log("Error getting teacher info: " . $e->getMessage());
            return false;
        }
    }
    
   
    public function getTeacherProjectsSingleTable($teacherId, $filters = []) {
        try {
            // Debug info
            error_log("Fetching projects for teacher ID: " . $teacherId);
            
            if (!$this->tableExists('projects')) {
                error_log("Projects table does not exist. Creating table.");
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS projects (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        description TEXT,
                        start_date DATE,
                        end_date DATE,
                        status VARCHAR(50) DEFAULT 'pending',
                        teacher_id INT NOT NULL,
                        teacher_name VARCHAR(255),
                        teacher_email VARCHAR(255),
                        teacher_department VARCHAR(255),
                        student_ids TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
                return [];
            }

            // Build the query with filters
            $sql = "SELECT * FROM projects WHERE teacher_id = :teacher_id";
            $params = [':teacher_id' => $teacherId];
            
            // Add status filter
            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Add search filter
            if (!empty($filters['search'])) {
                $sql .= " AND (name LIKE :search OR description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Add date range filter
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $sql .= " AND ((start_date BETWEEN :date_from AND :date_to) OR 
                               (end_date BETWEEN :date_from AND :date_to) OR
                               (start_date <= :date_from AND end_date >= :date_to))";
                $params[':date_from'] = $filters['date_from'];
                $params[':date_to'] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($projects) . " projects for teacher ID: " . $teacherId);
            
            // Deserialize student IDs and get student info
            foreach ($projects as &$project) {
                $studentIds = json_decode($project['student_ids'] ?? '[]', true);
                $project['student_count'] = count($studentIds);
                $project['students'] = $this->getStudentsInfo($studentIds);
            }
            
            return $projects;
        } catch (PDOException $e) {
            error_log("Error getting teacher projects: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get students information by IDs
     * @param array $studentIds Array of student IDs
     * @return array Students information
     */
    public function getStudentsInfo($studentIds) {
        if (empty($studentIds)) {
            return [];
        }
        
        try {
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $stmt = $this->db->prepare("
                SELECT id, name, email 
                FROM users 
                WHERE id IN ($placeholders) AND role = 'student'
            ");
            
            foreach ($studentIds as $index => $id) {
                $stmt->bindValue($index + 1, $id);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting students info: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get students in a project
     * @param int $projectId Project ID
     * @return array Students in the project
     */
    public function getProjectStudents($projectId) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email
                FROM users u
                JOIN project_group_members pgm ON u.id = pgm.user_id
                WHERE pgm.project_group_id = :project_id
                ORDER BY u.name
            ");
            $stmt->bindParam(':project_id', $projectId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting project students: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a table exists in the database
     * @param string $tableName The name of the table to check
     * @return bool True if table exists, false otherwise
     */
    private function tableExists($tableName) {
        try {
            // This works for MySQL and MariaDB
            $stmt = $this->db->prepare("
                SELECT 1 FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = ?
            ");
            $stmt->execute([$tableName]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log("Error checking if table exists: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ensure all required tables exist
     * @return bool Success status
     */
    public function ensureTablesExist() {
        try {
            // Create users table if it doesn't exist
            if (!$this->tableExists('users')) {
                $this->db->exec("
                    CREATE TABLE users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        role VARCHAR(50) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        last_login TIMESTAMP NULL
                    )
                ");
            }
            
            // Create projects table if it doesn't exist
            if (!$this->tableExists('projects')) {
                $this->db->exec("
                    CREATE TABLE projects (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        description TEXT,
                        start_date DATE,
                        end_date DATE,
                        status VARCHAR(50) DEFAULT 'pending',
                        teacher_id INT NOT NULL,
                        teacher_info TEXT,
                        student_ids TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
            }
            
            // Create diary_entries table if it doesn't exist
            if (!$this->tableExists('diary_entries')) {
                $this->db->exec("
                    CREATE TABLE diary_entries (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        project_id INT NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        content TEXT,
                        reviewed BOOLEAN DEFAULT 0,
                        feedback TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
            }
            
            // Create notifications table if it doesn't exist
            if (!$this->tableExists('notifications')) {
                $this->db->exec("
                    CREATE TABLE notifications (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        message TEXT,
                        link VARCHAR(255),
                        is_read BOOLEAN DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a table exists
     * @param string $table Table name
     * @return bool Whether table exists
     */
    public function checkTableExists($table) {
        return $this->tableExists($table);
    }
    
    /**
     * Fix teacher role for a user
     * @param int $userId User ID
     * @return bool Success status
     */
    public function fixTeacherRole($userId) {
        try {
            // Ensure users table exists
            if (!$this->tableExists('users')) {
                $this->db->exec("
                    CREATE TABLE users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        role VARCHAR(50) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        last_login TIMESTAMP NULL
                    )
                ");
            }
            
            // Check if user exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                // Update user's role to teacher
                $stmt = $this->db->prepare("UPDATE users SET role = 'teacher' WHERE id = :id");
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
            } else {
                // Create a new teacher user if ID doesn't exist
                $name = "Teacher";
                $email = "teacher" . $userId . "@example.com";
                $password = password_hash("teacher123", PASSWORD_DEFAULT);
                $role = "teacher";
                
                $stmt = $this->db->prepare("
                    INSERT INTO users (id, name, email, password, role, created_at)
                    VALUES (:id, :name, :email, :password, :role, NOW())
                    ON DUPLICATE KEY UPDATE role = :role
                ");
                
                $stmt->bindParam(':id', $userId);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':role', $role);
                $stmt->execute();
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error fixing teacher role: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get diary entry details
     * @param int $entryId Entry ID
     * @param int $teacherId Teacher ID for verification
     * @return array|bool Entry details or false if not found/no permission
     */
    public function getDiaryEntryDetails($entryId, $teacherId) {
        try {
            // Check if diary_entries table exists
            if (!$this->tableExists('diary_entries')) {
                // Create diary_entries table
                $this->db->exec("
                    CREATE TABLE diary_entries (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        student_id INT NOT NULL,
                        project_id INT NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        content TEXT NOT NULL,
                        attachments TEXT NULL,
                        reviewed TINYINT(1) DEFAULT 0,
                        feedback TEXT NULL,
                        rating INT DEFAULT 0,
                        reviewed_at TIMESTAMP NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
                return false;
            }
            
            // Get the entry and verify it belongs to a project of this teacher
            $stmt = $this->db->prepare("
                SELECT de.*, p.teacher_id 
                FROM diary_entries de
                JOIN projects p ON de.project_id = p.id
                WHERE de.id = :entry_id
            ");
            $stmt->bindParam(':entry_id', $entryId);
            $stmt->execute();
            
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If entry not found or doesn't belong to this teacher's project
            if (!$entry || $entry['teacher_id'] != $teacherId) {
                return false;
            }
            
            return $entry;
        } catch (PDOException $e) {
            error_log("Error getting diary entry details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Provide feedback for a diary entry
     * @param int $entryId Entry ID
     * @param string $feedback Feedback text
     * @param int $rating Rating (1-5)
     * @param int $teacherId Teacher ID for verification
     * @return bool Success status
     */
    public function provideFeedback($entryId, $feedback, $rating, $teacherId) {
        try {
            // Verify entry belongs to a project of this teacher
            $entry = $this->getDiaryEntryDetails($entryId, $teacherId);
            
            if (!$entry) {
                return false;
            }
            
            // Update entry with feedback
            $stmt = $this->db->prepare("
                UPDATE diary_entries 
                SET feedback = :feedback, 
                    rating = :rating, 
                    reviewed = 1, 
                    reviewed_at = NOW() 
                WHERE id = :entry_id
            ");
            
            $stmt->bindParam(':feedback', $feedback);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':entry_id', $entryId);
            
            $result = $stmt->execute();
            
            // If successful, create notification for student
            if ($result) {
                $this->createNotification(
                    $entry['student_id'],
                    "Feedback Received",
                    "Your teacher has provided feedback on your diary entry: " . $entry['title'],
                    "index.php?page=view_diary_entry&id=" . $entryId
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error providing feedback: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a notification
     * @param int $userId User ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $link Notification link
     * @return bool Success status
     */
    public function createNotification($userId, $title, $message, $link) {
        try {
            // Check if notifications table exists
            if (!$this->tableExists('notifications')) {
                // Create notifications table
                $this->db->exec("
                    CREATE TABLE notifications (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        message TEXT NOT NULL,
                        link VARCHAR(255) NULL,
                        is_read TINYINT(1) DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, link, created_at)
                VALUES (:user_id, :title, :message, :link, NOW())
            ");
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':link', $link);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get student details
     * @param int $studentId Student ID
     * @return array|bool Student details or false if not found
     */
    public function getStudentDetails($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email, role
                FROM users
                WHERE id = :id AND role = 'student'
            ");
            $stmt->bindParam(':id', $studentId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting student details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total diary entries by a student in a project
     * @param int $studentId Student ID
     * @param int $projectId Project ID (0 for all projects)
     * @return int Total entries
     */
    public function getStudentEntryCount($studentId, $projectId = 0) {
        try {
            $sql = "
                SELECT COUNT(*) FROM diary_entries
                WHERE student_id = :student_id
            ";
            
            if ($projectId > 0) {
                $sql .= " AND project_id = :project_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            
            if ($projectId > 0) {
                $stmt->bindParam(':project_id', $projectId);
            }
            
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting student entry count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get reviewed diary entries by a student in a project
     * @param int $studentId Student ID
     * @param int $projectId Project ID (0 for all projects)
     * @return int Reviewed entries
     */
    public function getStudentReviewedEntryCount($studentId, $projectId = 0) {
        try {
            $sql = "
                SELECT COUNT(*) FROM diary_entries
                WHERE student_id = :student_id AND reviewed = 1
            ";
            
            if ($projectId > 0) {
                $sql .= " AND project_id = :project_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            
            if ($projectId > 0) {
                $stmt->bindParam(':project_id', $projectId);
            }
            
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting student reviewed entry count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get average rating for a student's entries in a project
     * @param int $studentId Student ID
     * @param int $projectId Project ID (0 for all projects)
     * @return float Average rating
     */
    public function getStudentAverageRating($studentId, $projectId = 0) {
        try {
            $sql = "
                SELECT AVG(rating) FROM diary_entries
                WHERE student_id = :student_id AND reviewed = 1 AND rating > 0
            ";
            
            if ($projectId > 0) {
                $sql .= " AND project_id = :project_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            
            if ($projectId > 0) {
                $stmt->bindParam(':project_id', $projectId);
            }
            
            $stmt->execute();
            $avg = $stmt->fetchColumn();
            return $avg ? (float)$avg : 0;
        } catch (PDOException $e) {
            error_log("Error getting student average rating: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get project details
     * @param int $projectId Project ID
     * @param int $teacherId Teacher ID for verification
     * @return array|false Project details or false if not found/not authorized
     */
    public function getProjectDetails($projectId, $teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM projects
                WHERE id = :project_id AND teacher_id = :teacher_id
            ");
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindParam(':teacher_id', $teacherId);
            $stmt->execute();
            
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$project) {
                return false;
            }
            
            return $project;
        } catch (PDOException $e) {
            error_log("Error getting project details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get diary entries for a specific project
     * @param int $projectId Project ID
     * @param int $studentId Optional student ID filter
     * @param string $status Optional status filter ('reviewed', 'pending')
     * @return array Diary entries
     */
    public function getProjectDiaryEntries($projectId, $studentId = 0, $status = '') {
        try {
            // Check if diary_entries table exists
            if (!$this->tableExists('diary_entries')) {
                $this->createDiaryEntriesTable();
                return [];
            }
            
            // Build the query
            $sql = "
                SELECT 
                    d.id, d.title, d.content, d.created_at, d.reviewed, d.feedback,
                    u.id AS student_id, u.name AS student_name, u.email AS student_email
                FROM diary_entries d
                JOIN users u ON d.user_id = u.id
                WHERE d.project_id = :project_id
            ";
            
            $params = [':project_id' => $projectId];
            
            if ($studentId > 0) {
                $sql .= " AND d.user_id = :student_id";
                $params[':student_id'] = $studentId;
            }
            
            if ($status === 'reviewed') {
                $sql .= " AND d.reviewed = 1";
            } elseif ($status === 'pending') {
                $sql .= " AND (d.reviewed = 0 OR d.reviewed IS NULL)";
            }
            
            $sql .= " ORDER BY d.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting project diary entries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create diary_entries table if it doesn't exist
     */
    private function createDiaryEntriesTable() {
        try {
            $this->db->exec("
                CREATE TABLE diary_entries (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    project_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    content TEXT,
                    attachments TEXT,
                    reviewed TINYINT(1) DEFAULT 0,
                    feedback TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            return true;
        } catch (PDOException $e) {
            error_log("Error creating diary_entries table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get project by ID
     * @param int $projectId Project ID
     * @return array|false Project details or false if not found
     */
    public function getProjectById($projectId) {
        try {
            // First check the projects table (single table approach)
            if ($this->tableExists('projects')) {
                $stmt = $this->db->prepare("
                    SELECT * FROM projects
                    WHERE id = :project_id
                ");
                $stmt->bindParam(':project_id', $projectId);
                $stmt->execute();
                
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($project) {
                    // Add decoded student IDs
                    $project['student_ids_array'] = json_decode($project['student_ids'] ?? '[]', true);
                    return $project;
                }
            }
            
            // Fallback to project_groups table
            if ($this->tableExists('project_groups')) {
                $stmt = $this->db->prepare("
                    SELECT * FROM project_groups
                    WHERE id = :project_id
                ");
                $stmt->bindParam(':project_id', $projectId);
                $stmt->execute();
                
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($project) {
                    // Get student IDs associated with this project
                    $stmt = $this->db->prepare("
                        SELECT user_id FROM project_group_members
                        WHERE project_group_id = :project_id
                    ");
                    $stmt->bindParam(':project_id', $projectId);
                    $stmt->execute();
                    
                    $studentIds = [];
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $studentIds[] = $row['user_id'];
                    }
                    $project['student_ids_array'] = $studentIds;
                    return $project;
                }
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error getting project by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Submit feedback for a diary entry
     * @param int $entryId Diary entry ID
     * @param string $feedback Feedback text
     * @return bool Success status
     */
    public function submitFeedback($entryId, $feedback) {
        try {
            $stmt = $this->db->prepare("
                UPDATE diary_entries
                SET feedback = :feedback, reviewed = 1
                WHERE id = :entry_id
            ");
            $stmt->bindParam(':feedback', $feedback);
            $stmt->bindParam(':entry_id', $entryId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error submitting feedback: " . $e->getMessage());
            return false;
        }
    }
}
?>