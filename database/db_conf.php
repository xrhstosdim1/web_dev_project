<?php
// Database connection settings
$servername = "localhost"; // Host (localhost by default)
$username = "root"; // MySQL username (default for XAMPP)
$password = ""; // MySQL password (default for XAMPP)
$dbname = "project_web"; // Your database name

// Create the connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
