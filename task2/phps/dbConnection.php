<?php
// Database Configuration
// Host 
$host = 'localhost';

// Name of your database
$db = 'AstroGallery';

// Database username
$user = 'root';

// Database password
$pass = '';

// Character encoding for the database connection
$charset = 'utf8mb4';

// DSN (Data Source Name) string defining connection details
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO configuration options
$options = [
    // Throw exceptions if a database error occurs
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

    // Fetch results as associative arrays (column names as array keys)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // Use real prepared statements (prevents SQL injection)
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Create a new PDO database connection using DSN and settings
    $pdo = new PDO($dsn, $user, $pass, $options);

    //echo "Database connection successful!"; //uncomment to test DB connection

} catch (PDOException $e) {
    // If connection fails, stop script and show error
    die("Database connection failed: " . $e->getMessage());
}
?>