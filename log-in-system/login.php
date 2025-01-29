<?php
session_start();

include('../database/db_conf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);    
    $password = trim($_POST['password']);  

    $stmt = $conn->prepare("SELECT password, name, surname FROM User WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($db_password, $name, $surname);
        $stmt->fetch();

        // Verify password
        if ($password === $db_password) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            $_SESSION['surname'] = $surname;

            // Determine role
            if (str_starts_with($email, 'st')) {
                $_SESSION['role'] = 'student';
                header("Location: ../student/student.php");
            } elseif (str_starts_with($email, 'sec_')) {
                $_SESSION['role'] = 'secretary';
                header("Location: ../secretary/secretary.php");
            } else {
                $_SESSION['role'] = 'professor';
                header("Location: ../professor/professor.php");
            }
            exit();
        } else {
            // Incorrect password
            header("Location: ../log-in-system/login.html?error=invalid_credentials&email=" . urlencode($email));
            exit();
        }
    } else {
        // Email not found
        header("Location: ../log-in-system/login.html?error=user_not_found");
        exit();
    }
}

// Redirect if accessed directly without POST data
header("Location: ../login.html");
exit();
?>
