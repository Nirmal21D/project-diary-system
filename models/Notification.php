<?php

class Notification {
    private $id;
    private $user_id;
    private $message;
    private $seen;

    public function __construct($user_id, $message) {
        $this->user_id = $user_id;
        $this->message = $message;
        $this->seen = false; // Default to not seen
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getMessage() {
        return $this->message;
    }

    public function isSeen() {
        return $this->seen;
    }

    public function markAsSeen() {
        $this->seen = true;
    }

    public function save() {
        // Code to save the notification to the database
    }

    public static function getAllByUserId($user_id) {
        // Code to retrieve all notifications for a specific user from the database
    }
}