<?php
// config.php – Database configuration for WildMatch Projekt 2

// Prevent direct access (optional but good practice)
if (basename($_SERVER['PHP_SELF']) === 'config.php') {
    http_response_code(403);
    die('Direct access forbidden.');
}

// Database credentials (Arcada-specific)
$host = 'localhost';
$db   = 'abdullan';          // Your Arcada MySQL database name
$user = 'abdullan';          // Your Arcada username
$pass = '';                  // Leave empty unless you set a MySQL password
$charset = 'utf8mb4';

// DSN and options
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Create PDO instance
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    die("Databasanslutning misslyckades. Försök igen senare.");
}