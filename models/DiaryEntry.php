<?php

class DiaryEntry {
    public $id;
    public $group_id;
    public $user_id;
    public $content;
    public $status;

    public function __construct($group_id, $user_id, $content, $status = 'pending') {
        $this->group_id = $group_id;
        $this->user_id = $user_id;
        $this->content = $content;
        $this->status = $status;
    }

    public function createEntry($conn) {
        $stmt = $conn->prepare("INSERT INTO project_diary (group_id, user_id, content, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $this->group_id, $this->user_id, $this->content, $this->status);
        return $stmt->execute();
    }

    public function updateEntry($conn, $id) {
        $stmt = $conn->prepare("UPDATE project_diary SET content = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $this->content, $this->status, $id);
        return $stmt->execute();
    }

    public static function getEntriesByGroup($conn, $group_id) {
        $stmt = $conn->prepare("SELECT * FROM project_diary WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getEntryById($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM project_diary WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}