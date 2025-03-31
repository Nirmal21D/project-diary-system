<?php

class ProjectGroup {
    private $id;
    private $teacher_id;
    private $name;

    public function __construct($teacher_id, $name) {
        $this->teacher_id = $teacher_id;
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function getTeacherId() {
        return $this->teacher_id;
    }

    public function getName() {
        return $this->name;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTeacherId($teacher_id) {
        $this->teacher_id = $teacher_id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function save() {
        // Code to save the project group to the database
    }

    public function update() {
        // Code to update the project group in the database
    }

    public function delete() {
        // Code to delete the project group from the database
    }

    public static function getAllGroups() {
        // Code to retrieve all project groups from the database
    }

    public static function getGroupById($id) {
        // Code to retrieve a project group by its ID from the database
    }
}

?>