<?php
session_start();
include('../database/db_conf.php');

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['q'])) {
    $searchTerm = '%' . $_GET['q'] . '%';

    $stmt = $conn->prepare("SELECT am, name, surname FROM Students 
                            INNER JOIN User ON Students.email = User.email
                            WHERE am LIKE ? OR name LIKE ? OR surname LIKE ?");
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);

    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            'am' => $row['am'],
            'name' => $row['name'] . ' ' . $row['surname']
        ];
    }

    echo json_encode($students);
} else {
    echo json_encode(['error' => 'Λείπει το query parameter']);
}

$conn->close();
exit();
?>