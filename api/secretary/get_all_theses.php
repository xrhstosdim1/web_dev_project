<?php
include('../../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';

    $query = "
        SELECT 
            dk.id, 
            dk.topic, 
            dk.status, 
            df.am_foititi, 
            CONCAT(u.name, ' ', u.surname) AS student_name
        FROM 
            diplwmatiki_ka8igita dk
        LEFT JOIN 
            diplwmatiki_foitita df 
            ON dk.id = df.id_diplwmatikis
        LEFT JOIN 
            Students s 
            ON df.am_foititi = s.am
        LEFT JOIN 
            User u 
            ON s.email = u.email
    ";

    if ($statusFilter !== 'all') {
        $query .= " WHERE dk.status = ?";
    }

    $query .= " ORDER BY dk.id DESC";

    $stmt = $conn->prepare($query);

    if ($statusFilter !== 'all') {
        $stmt->bind_param('s', $statusFilter);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $theses = array();

        while ($row = $result->fetch_assoc()) {
            $theses[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $theses]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκαν δεδομένα.']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>