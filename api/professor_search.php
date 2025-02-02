<?php
session_start();
include('../database/db_conf.php');

header('Content-Type: application/json');
//error_reporting(E_ALL); //debugggg
//ini_set('display_errors', 1); /debugg

if (isset($_GET['q'])) {
    $searchTerm = '%' . $_GET['q'] . '%';

    $stmt = $conn->prepare("SELECT professor.email, user.name, user.surname 
                            FROM professor 
                            INNER JOIN user ON professor.email = user.email
                            WHERE professor.email LIKE ? OR user.name LIKE ? OR user.surname LIKE ?");
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);

    $stmt->execute();
    $result = $stmt->get_result();

    $professors = [];
    while ($row = $result->fetch_assoc()) {
        $professors[] = [
            'email' => $row['email'],
            'name' => $row['name'] . ' ' . $row['surname']
        ];
    }

    echo json_encode($professors);
} else {
    echo json_encode(['error' => 'Λείπει το query parameter']);
}

$conn->close();
exit();
?>