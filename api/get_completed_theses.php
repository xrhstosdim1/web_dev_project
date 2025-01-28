<?php
include('../database/db_conf.php');
header('Content-Type: application/json; charset=utf-8');

try {
    $query = "
        SELECT 
            dk.topic AS thesis_topic,
            CONCAT(u_prof.name, ' ', u_prof.surname) AS professor_name,
            CONCAT(u_stud.name, ' ', u_stud.surname) AS student_name,
            dk.completion_date AS submission_date,
            df.nemertes_link AS nemertes_link
        FROM diplwmatiki_ka8igita dk
        INNER JOIN professor p ON dk.email = p.email
        INNER JOIN User u_prof ON p.email = u_prof.email
        INNER JOIN diplwmatiki_foitita df ON dk.id = df.id_diplwmatikis
        INNER JOIN Students s ON df.am_foititi = s.am
        INNER JOIN User u_stud ON s.email = u_stud.email
        WHERE dk.status = 'oloklirwmeni'
        ORDER BY dk.completion_date ASC;
    ";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Σφάλμα κατά την εκτέλεση του query: " . $conn->error);
    }

    $theses = [];
    while ($row = $result->fetch_assoc()) {
        $theses[] = [
            'topic' => $row['thesis_topic'],
            'professor_name' => $row['professor_name'],
            'student_name' => $row['student_name'],
            'submission_date' => $row['submission_date'],
            'nemertes_link' => $row['nemertes_link']
        ];
    }

    echo json_encode(['success' => true, 'theses' => $theses]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά τη φόρτωση των ολοκληρωμένων διπλωματικών: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
