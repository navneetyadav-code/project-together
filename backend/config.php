<?php
// db.php - Handles Database Connection and Initialization

$host = 'sql100.infinityfree.com';
$dbname = 'if0_41650456_projecthun'; // Your database name
$username = 'if0_41650456'; // Change if you have a specific DB user
$password = 'WrZyeiySYvUhlj'; // Change if you have a DB password

try {
    // Connect to MySQL server first to ensure DB exists
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");

    // Create the users table dynamically if it doesn't exist
    $tableQuery = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(50) DEFAULT 'patient',
        full_name VARCHAR(100) NOT NULL,
        dob DATE NOT NULL,
        age INT NOT NULL,
        gender VARCHAR(20) NOT NULL,
        blood_group VARCHAR(10),
        phone VARCHAR(15) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($tableQuery);

} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database Connection Failed: " . $e->getMessage()]));
}
?>