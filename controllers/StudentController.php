<?php

class StudentController {
    
    private $diaryEntryModel;
    private $notificationModel;

    public function __construct() {
        // Load models
        $this->diaryEntryModel = new DiaryEntry();
        $this->notificationModel = new Notification();
    }

    public function submitDiaryEntry($data) {
        // Validate and submit diary entry
        $entryId = $this->diaryEntryModel->createEntry($data);
        if ($entryId) {
            $this->notificationModel->createNotification($data['user_id'], "Diary entry submitted successfully.");
            return true;
        }
        return false;
    }

    public function updateDiaryEntry($entryId, $data) {
        // Validate and update diary entry
        $updated = $this->diaryEntryModel->updateEntry($entryId, $data);
        if ($updated) {
            $this->notificationModel->createNotification($data['user_id'], "Diary entry updated successfully.");
            return true;
        }
        return false;
    }

    public function getDiaryEntries($userId) {
        // Retrieve diary entries for the student
        return $this->diaryEntryModel->getEntriesByUserId($userId);
    }

    public function getNotifications($userId) {
        // Retrieve notifications for the student
        return $this->notificationModel->getNotificationsByUserId($userId);
    }
}