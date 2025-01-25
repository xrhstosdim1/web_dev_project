<?php
session_start();
include('../database/db_conf.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No logged-in users.']);
    exit();
}

$name = $_SESSION['name'];
$surname = $_SESSION['surname'];

if ($name && $surname) {
    echo json_encode([
        'success' => true,
        'data' => [
            'name' => $name,
            'surname' => $surname
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to fetch user details.'
    ]);
}

exit();
?>