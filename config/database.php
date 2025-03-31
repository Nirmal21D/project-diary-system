<?php
// Database configuration
$dbHost = 'localhost';
$dbName = 'micro'; // Adjust this to match your actual database name
$dbUsername = 'root'; // Default XAMPP username
$dbPassword = ''; // Default XAMPP password is empty

// Make these variables available globally if needed
global $dbHost, $dbName, $dbUsername, $dbPassword;

// You can also define a function to get the database connection
function getDbConnection() {
    global $dbHost, $dbName, $dbUsername, $dbPassword;
    
    try {
        $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed: " . $e->getMessage());
    }
}
?>