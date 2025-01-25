<?php
include('../database/db_conf.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $diplomaId = isset($_GET['id_diplomatikis']) ? $_GET['id_diplomatikis'] : 0;

    $query = "
        SELECT 
            er.id AS request_id,
            er.id_diplomatikis,
            er.prof_email,
            dk.topic AS thesis_topic,
            u_student.name AS student_name,
            u_student.surname AS student_surname,
            u_prof.name AS professor_name,
            u_prof.surname AS professor_surname,
            er.status,
            er.date_requested,
            er.date_answered
        FROM 
            epivlepontes_requests er
        JOIN 
            diplwmatiki_ka8igita dk ON er.id_diplomatikis = dk.id
        JOIN 
            Students s ON er.student_am = s.am
        JOIN 
            User u_student ON s.email = u_student.email
        LEFT JOIN 
            User u_prof ON er.prof_email = u_prof.email
        WHERE 
            er.id_diplomatikis = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $diplomaId);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'request_id'        => $row['request_id'],
            'id_diplomatikis'   => $row['id_diplomatikis'],
            'thesis_topic'      => $row['thesis_topic'],
            'student_name'      => $row['student_name'] . ' ' . $row['student_surname'],
            'professor_name'    => $row['professor_name'] . ' ' . $row['professor_surname'],
            'status'            => $row['status'],
            'date_requested'    => $row['date_requested'],
            'date_answered'     => $row['date_answered']
        ];
    }

    echo json_encode(['success' => true, 'requests' => $requests]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>