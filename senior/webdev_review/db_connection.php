<?php
// Enable displaying of errors for easier debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Function to establish a PDO connection to a MySQL database
// (Replace connection details with actual credentials and consider security best practices)
function db_connect(): PDO
{
    // Connection details (**IMPORTANT: UPDATE THEM!**)
    $host = '127.0.0.1';
    $db_name = 'eh_db'; // Replace with your actual database name
    $db_user = 'root'; // Replace with your actual database username
    $db_password = 'password'; // Replace with your actual database password

    // Construct the PDO connection string using UTF-8 encoding
    $db = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";

    // Set PDO error mode to throw exceptions for better handling
    $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

    // Attempt to connect to the database
    try {
        $pdo = new PDO($db, $db_user, $db_password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Handle connection error appropriately, providing additional context
        // You may want to log the error or display a user-friendly message
        throw new RuntimeException("Database connection failed: " . $e->getMessage());
    }
}