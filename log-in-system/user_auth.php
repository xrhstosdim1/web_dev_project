<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login if not logged in
    header("Location: ../log-in-system/login.html?error=not-logged-in");
    exit();
}

// Check if the role matches the required role for the page
$current_page = basename($_SERVER['PHP_SELF']);

//debugggg
// echo '<pre>';
// print_r($_SESSION);  // This will show all session parameters
// echo '</pre>';

if ($current_page == "student.php" && $_SESSION['role'] !== 'student') {
    header("Location: ../log-in-system/login.html?error=access-denied"); // Redirect to login if not a student
    exit();
}

if ($current_page == "professor.php" && $_SESSION['role'] !== 'professor') {
    header("Location: ../log-in-system/login.html?error=access-denied"); // Redirect to login if not a professor
    exit();
}

if ($current_page == "secretary.php" && $_SESSION['role'] !== 'secretary') {
    header("Location: ../log-in-system/login.html?error=access-denied"); // Redirect to login if not a secretary
    exit();
}
?>