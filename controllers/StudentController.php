<?php
// filepath: c:\xampp\htdocs\project-diary-system\controllers\StudentController.php

class StudentController {
    private $db;
    private $lastError;

    public function __construct($db) {
        $this->db = $db;
    }

    public function setLastError($error) {
        $this->lastError = $error;
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Get student information
     */
    public function getStudentInfo($studentId) {
        try {
            $query = "SELECT * FROM users WHERE id = :id AND role = 'student'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getStudentInfo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all projects for a student
     */
    public function getStudentProjects($studentId) {
        try {
            $query = "
                SELECT p.id, p.name, p.description, p.status, p.teacher_id, 
                       u.name as teacher_name
                FROM projects p
                JOIN users u ON p.teacher_id = u.id
                WHERE p.student_ids LIKE :pattern
                ORDER BY p.name ASC
            ";
            
            // Use JSON pattern matching
            $pattern = '%"' . $studentId . '"%';
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':pattern', $pattern, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStudentProjects: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project details by ID
     */
    public function getProjectById($projectId, $studentId) {
        try {
            $query = "
                SELECT p.id, p.name, p.description, p.status, 
                      p.teacher_id, p.student_ids, p.created_at,
                      u.name as teacher_name
                FROM projects p
                JOIN users u ON p.teacher_id = u.id
                WHERE p.id = :project_id
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
            
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                return false;
            }
            
            // Verify student has access to this project
            $authorized = false;
            
            try {
                $studentIds = json_decode($project['student_ids'], true);
                if ($studentIds === null) {
                    $studentIds = explode(',', $project['student_ids']);
                    $studentIds = array_map('trim', $studentIds);
                }
                
                if (is_array($studentIds) && (in_array($studentId, $studentIds) || in_array((string)$studentId, $studentIds))) {
                    $authorized = true;
                }
            } catch (Exception $e) {
                error_log("Error checking project authorization: " . $e->getMessage());
                return false;
            }
            
            if (!$authorized) {
                return false;
            }
            
            return $project;
        } catch (PDOException $e) {
            error_log("Database error in getProjectById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all diary entries for a student
     */
    public function getStudentDiaryEntries($studentId, $projectGroupId = null) {
        try {
            $params = [':student_id' => $studentId];
            
            $query = "
                SELECT d.*, pg.name as project_name, u.name as teacher_name
                FROM diary_entries d
                JOIN project_groups pg ON d.project_group_id = pg.id
                LEFT JOIN users u ON pg.teacher_id = u.id
                WHERE d.user_id = :student_id
            ";
            
            if ($projectGroupId) {
                $query .= " AND d.project_group_id = :project_group_id";
                $params[':project_group_id'] = $projectGroupId;
            }
            
            $query .= " ORDER BY d.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStudentDiaryEntries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new diary entry
     */
    public function createDiaryEntry($studentId, $projectId, $title, $content, $date, $hoursSpent) {
        try {
            // Validate project exists - use project_id directly with projects table
            $projectQuery = "SELECT * FROM projects WHERE id = :project_id";
            $stmt = $this->db->prepare($projectQuery);
            $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
            
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$project) {
                $this->setLastError("Project not found");
                return false;
            }
            
            // Check if student has access to this project
            $hasAccess = false;
            $studentIds = json_decode($project['student_ids'], true);
            
            // If JSON decode failed, try comma-separated format
            if ($studentIds === null) {
                $studentIds = array_map('trim', explode(',', $project['student_ids']));
            }
            
            if (is_array($studentIds) && in_array($studentId, $studentIds)) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                $this->setLastError("You don't have permission to create entries for this project");
                return false;
            }
            
            // Get or create corresponding project group
            $projectGroupId = $this->getOrCreateProjectGroup($projectId, $studentId);
            
            if (!$projectGroupId) {
                $this->setLastError("Failed to find or create project group");
                return false;
            }
            
            // Insert the diary entry with the correct project_group_id
            $stmt = $this->db->prepare("
                INSERT INTO diary_entries (user_id, project_group_id, title, content, entry_date, hours_spent, created_at)
                VALUES (:student_id, :project_group_id, :title, :content, :entry_date, :hours_spent, NOW())
            ");
            
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':project_group_id', $projectGroupId, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':entry_date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':hours_spent', $hoursSpent, PDO::PARAM_STR);
            
            $stmt->execute();
            $entryId = $this->db->lastInsertId();
            
            if (!$entryId) {
                $this->setLastError("Failed to get new entry ID");
                return false;
            }

            $_SESSION['success_message'] = "Diary entry created successfully.";
            header('Location: index.php?page=diary_entries');
            exit;
        } catch (PDOException $e) {
            $this->setLastError($e->getMessage());
            error_log("PDO Error creating diary entry: " . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            error_log("General error creating diary entry: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a diary entry
     */
    public function updateDiaryEntry($entryId, $studentId, $title, $content, $date, $hoursSpent) {
        try {
            // First check if the entry exists and belongs to the student
            $query = "SELECT * FROM diary_entries WHERE id = :entry_id AND user_id = :student_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':entry_id', $entryId, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$entry) {
                $this->setLastError("Entry not found or you don't have permission to edit it");
                return false;
            }
            
            // Don't allow editing reviewed entries
            if ($entry['reviewed']) {
                $this->setLastError("You cannot edit entries that have already been reviewed");
                return false;
            }
            
            // Update the entry
            $query = "
                UPDATE diary_entries 
                SET title = :title, 
                    content = :content, 
                    entry_date = :entry_date, 
                    hours_spent = :hours_spent,
                    updated_at = NOW()
                WHERE id = :entry_id AND user_id = :student_id
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':entry_date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':hours_spent', $hoursSpent, PDO::PARAM_STR);
            $stmt->bindParam(':entry_id', $entryId, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            
            $stmt->execute();

            $_SESSION['success_message'] = "Diary entry updated successfully.";
            header('Location: index.php?page=diary_entries');
            exit;
        } catch (PDOException $e) {
            $this->setLastError($e->getMessage());
            error_log("Error updating diary entry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a diary entry
     */
    public function deleteDiaryEntry($entryId, $studentId) {
        try {
            // First check if the entry exists and belongs to the student
            $query = "SELECT * FROM diary_entries WHERE id = :entry_id AND user_id = :student_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':entry_id', $entryId, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$entry) {
                $this->setLastError("Entry not found or you don't have permission to delete it");
                return false;
            }
            
            // Don't allow deleting reviewed entries
            if ($entry['reviewed']) {
                $this->setLastError("You cannot delete entries that have already been reviewed");
                return false;
            }
            
            // Delete the entry
            $query = "DELETE FROM diary_entries WHERE id = :entry_id AND user_id = :student_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':entry_id', $entryId, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            
            $stmt->execute();

            $_SESSION['success_message'] = "Diary entry deleted successfully.";
            header('Location: index.php?page=diary_entries');
            exit;
        } catch (PDOException $e) {
            $this->setLastError($e->getMessage());
            error_log("Error deleting diary entry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get or create a project group for a project
     */
    private function getOrCreateProjectGroup($projectId, $studentId) {
        try {
            // First check if there's an existing project group for this project
            $query = "
                SELECT pg.id 
                FROM project_groups pg
                JOIN projects p ON pg.name = p.name
                WHERE p.id = :project_id
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
            
            $projectGroupId = $stmt->fetchColumn();
            
            if ($projectGroupId) {
                // Check if student is a member of this project group
                $memberQuery = "SELECT COUNT(*) FROM project_group_members 
                               WHERE project_group_id = :project_group_id AND user_id = :user_id";
                $stmt = $this->db->prepare($memberQuery);
                $stmt->bindParam(':project_group_id', $projectGroupId, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $studentId, PDO::PARAM_INT);
                $stmt->execute();
                
                $isMember = ($stmt->fetchColumn() > 0);
                
                if (!$isMember) {
                    // Add student to project group
                    $addMemberQuery = "INSERT INTO project_group_members (project_group_id, user_id) 
                                      VALUES (:project_group_id, :user_id)";
                    $stmt = $this->db->prepare($addMemberQuery);
                    $stmt->bindParam(':project_group_id', $projectGroupId, PDO::PARAM_INT);
                    $stmt->bindParam(':user_id', $studentId, PDO::PARAM_INT);
                    $stmt->execute();
                }
                
                return $projectGroupId;
            }
            
            // No existing project group, create a new one
            // Get project details
            $projectQuery = "SELECT name, description, teacher_id FROM projects WHERE id = :project_id";
            $stmt = $this->db->prepare($projectQuery);
            $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
            $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$projectData) {
                return false;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Create new project group
                $createGroupQuery = "
                    INSERT INTO project_groups (name, teacher_id, description, status)
                    VALUES (:name, :teacher_id, :description, 'active')
                ";
                
                $stmt = $this->db->prepare($createGroupQuery);
                $stmt->bindParam(':name', $projectData['name'], PDO::PARAM_STR);
                $stmt->bindParam(':teacher_id', $projectData['teacher_id'], PDO::PARAM_INT);
                $stmt->bindParam(':description', $projectData['description'], PDO::PARAM_STR);
                $stmt->execute();
                
                $newProjectGroupId = $this->db->lastInsertId();
                
                // Add student to project group members
                $memberQuery = "
                    INSERT INTO project_group_members (project_group_id, user_id)
                    VALUES (:project_group_id, :user_id)
                ";
                
                $stmt = $this->db->prepare($memberQuery);
                $stmt->bindParam(':project_group_id', $newProjectGroupId, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $studentId, PDO::PARAM_INT);
                $stmt->execute();
                
                $this->db->commit();
                return $newProjectGroupId;
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error creating project group: " . $e->getMessage());
                return false;
            }
        } catch (PDOException $e) {
            error_log("Database error in getOrCreateProjectGroup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: Get correct column name for project ID in diary_entries table
     */
    private function getDiaryEntryProjectColumnName() {
        try {
            // Check if project_id exists in diary_entries table
            $query = "SHOW COLUMNS FROM diary_entries LIKE 'project_id'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return 'project_id';
            }
            
            // Check if project_group_id exists
            $query = "SHOW COLUMNS FROM diary_entries LIKE 'project_group_id'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return 'project_group_id';
            }
            
            // Default to project_id if neither is found
            return 'project_id';
        } catch (PDOException $e) {
            error_log("Error checking diary_entries columns: " . $e->getMessage());
            return 'project_id'; // Default fallback
        }
    }

    /**
     * Get recent diary entries for a student
     */
    public function getRecentDiaryEntries($studentId, $limit = 5) {
        try {
            $columnName = $this->getDiaryEntryProjectColumnName();
            
            $query = "
                SELECT d.*, p.name as project_name 
                FROM diary_entries d
                JOIN projects p ON d.{$columnName} = p.id
                WHERE d.user_id = :student_id
                ORDER BY d.created_at DESC
                LIMIT :limit
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getRecentDiaryEntries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all pending reviews for a student
     */
    public function getPendingReviews($studentId) {
        try {
            $columnName = $this->getDiaryEntryProjectColumnName();
            
            $query = "
                SELECT d.*, p.name as project_name 
                FROM diary_entries d
                JOIN projects p ON d.{$columnName} = p.id
                WHERE d.user_id = :student_id AND d.reviewed = 0
                ORDER BY d.created_at DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getPendingReviews: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get counts for dashboard statistics
     */
    public function getEntryStatistics($studentId) {
        try {
            // Get total entries count
            $totalQuery = "SELECT COUNT(*) FROM diary_entries WHERE user_id = :student_id";
            $stmt = $this->db->prepare($totalQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $totalEntries = $stmt->fetchColumn();
            
            // Get reviewed entries count
            $reviewedQuery = "SELECT COUNT(*) FROM diary_entries WHERE user_id = :student_id AND reviewed = 1";
            $stmt = $this->db->prepare($reviewedQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $reviewedEntries = $stmt->fetchColumn();
            
            return [
                'totalEntries' => $totalEntries,
                'reviewedEntries' => $reviewedEntries
            ];
        } catch (PDOException $e) {
            error_log("Database error in getEntryStatistics: " . $e->getMessage());
            return [
                'totalEntries' => 0,
                'reviewedEntries' => 0
            ];
        }
    }

    /**
     * Get dashboard data for student
     */
    public function getDashboardData($studentId) {
        try {
            $data = [];
            
            // Get projects - from the projects table
            $projectsQuery = "
                SELECT p.id, p.name, p.description, p.status, p.teacher_id, u.name as teacher_name
                FROM projects p
                JOIN users u ON p.teacher_id = u.id
                WHERE p.student_ids LIKE :pattern
                ORDER BY p.name ASC
            ";
            
            $pattern = '%"' . $studentId . '"%';
            $stmt = $this->db->prepare($projectsQuery);
            $stmt->bindParam(':pattern', $pattern, PDO::PARAM_STR);
            $stmt->execute();
            $data['projects'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get recent diary entries - join with project_groups and projects
            $entriesQuery = "
                SELECT d.*, pg.name as project_name 
                FROM diary_entries d
                JOIN project_groups pg ON d.project_group_id = pg.id
                WHERE d.user_id = :student_id
                ORDER BY d.created_at DESC
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($entriesQuery);
            $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $data['recentEntries'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get pending reviews
            $pendingQuery = "
                SELECT d.*, pg.name as project_name 
                FROM diary_entries d
                JOIN project_groups pg ON d.project_group_id = pg.id
                WHERE d.user_id = :student_id AND d.reviewed = 0
                ORDER BY d.created_at DESC
            ";
            
            $stmt = $this->db->prepare($pendingQuery);
            $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $data['pendingReviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get stats
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM diary_entries WHERE user_id = :student_id");
            $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $data['totalEntries'] = $stmt->fetchColumn();
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM diary_entries WHERE user_id = :student_id AND reviewed = 1");
            $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $data['reviewedEntries'] = $stmt->fetchColumn();
            
            return $data;
        } catch (PDOException $e) {
            error_log("Error in getDashboardData: " . $e->getMessage());
            return [
                'projects' => [],
                'recentEntries' => [],
                'pendingReviews' => [],
                'totalEntries' => 0,
                'reviewedEntries' => 0
            ];
        }
    }

    /**
     * Get a single diary entry
     */
    public function getDiaryEntry($entryId, $studentId) {
        try {
            // Add debug statement
            error_log("Fetching entry ID: $entryId for student ID: $studentId");
            
            $stmt = $this->db->prepare("
                SELECT d.*, pg.name as project_name 
                FROM diary_entries d
                LEFT JOIN project_groups pg ON d.project_group_id = pg.id
                WHERE d.id = :entry_id AND d.user_id = :student_id
            ");
            $stmt->bindParam(':entry_id', $entryId, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Add debug statement
            error_log("Entry found: " . ($entry ? "Yes" : "No"));
            
            return $entry;
        } catch (PDOException $e) {
            error_log("Error in getDiaryEntry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all diary entries for a specific student
     * 
     * @param int $studentId The ID of the student
     * @return array An array of diary entries
     */
    public function getDiaryEntries($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT d.*, pg.name as project_name 
                FROM diary_entries d
                JOIN project_groups pg ON d.project_group_id = pg.id
                WHERE d.user_id = :student_id
                ORDER BY d.entry_date DESC
            ");
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting diary entries: " . $e->getMessage());
            throw new Exception("Database error while retrieving diary entries");
        }
    }
}
?>