<?php
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');
try {
    $query = "
        SELECT 
            dk.id, 
            dk.topic, 
            dk.summary, 
            u.name AS professor_name, 
            u.surname AS professor_surname
        FROM 
            diplwmatiki_ka8igita dk
        LEFT JOIN 
            professor p ON dk.email = p.email
        LEFT JOIN 
            User u ON p.email = u.email
        WHERE 
            dk.status = 'diathesimi'
        ORDER BY
            dk.id DESC
    ";

    $result = $conn->query($query);

    $theses = [];
    while ($row = $result->fetch_assoc()) {
        $theses[] = [
            'id' => $row['id'],
            'topic' => $row['topic'],
            'summary' => $row['summary'],
            'professor_name' => $row['professor_name'],
            'professor_surname' => $row['professor_surname']
        ];
    }

    echo json_encode(['success' => true, 'theses' => $theses]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>