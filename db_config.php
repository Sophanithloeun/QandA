<?php
// db_config.php
// Database configuration settings

// Database host (e.g., 'localhost' or '127.0.0.1')
define('DB_HOST', 'localhost');

// Database username
define('DB_USER', 'root'); // Replace with your database username

// Database password
define('DB_PASS', ''); // Replace with your database password

// Database name
define('DB_NAME', 'qna_system'); // Replace with your database name

// Attempt to connect to MySQL database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    // Log error to a file or use a more robust error handling mechanism for production
    error_log("Database connection failed: " . $mysqli->connect_error);
    // Display a user-friendly message
    die("Sorry, we're experiencing some technical difficulties. Please try again later.");
}

// Set character set to utf8mb4 for full Unicode support
if (!$mysqli->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $mysqli->error);
    // Optionally, you could die here as well if charset is critical
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
