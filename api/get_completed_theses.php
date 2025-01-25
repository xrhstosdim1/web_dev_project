<?php
include('../database/db_conf.php');
header('Content-Type: application/json; charset=utf-8');

try {
    $query = "
        SELECT 
            dk.topic AS thesis_topic,
            CONCAT(u_prof.name, ' ', u_prof.surname) AS professor_name,
            CONCAT(u_stud.name, ' ', u_stud.surname) AS student_name,
            g.cur_date AS submission_date,
            g.nemertes_link AS nemertes_link
        FROM gramateia g
        INNER JOIN diplwmatiki_ka8igita dk ON g.id_diplwmatikis = dk.id
        INNER JOIN professor p ON dk.email = p.email
        INNER JOIN User u_prof ON p.email = u_prof.email
        INNER JOIN Students s ON g.am_foititi = s.am
        INNER JOIN User u_stud ON s.email = u_stud.email
        WHERE g.aithsh_gia = 'pros_egrisi_oloklirwmenh' AND apanthsh = 'accepted'
        ORDER BY g.cur_date ASC;
    ";
//epistrefei oloklirwmenes apo grammateia

    $result = $conn->query($query);

    $theses = [];
    while ($row = $result->fetch_assoc()) {
        $theses[] = [
            'topic' => $row['thesis_topic'],
            'professor_name' => $row['professor_name'],
            'student_name' => $row['student_name'],
            'nemertes_link' => $row['nemertes_link']
        ];
    }

    echo json_encode(['success' => true, 'theses' => $theses]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά τη φόρτωση των ολοκληρωμένων διπλωματικών.']);
}
$conn->close();
?>