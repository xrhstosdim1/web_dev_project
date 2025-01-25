<?php

include('../database/db_conf.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['email'])) {
        echo json_encode(["success" => false, "message" => "Student email not found in session."]);
        exit;
    }
    $student_email = $_SESSION['email'];

    $am_query = "SELECT am FROM Students WHERE email = ?";
    $stmt = $conn->prepare($am_query);
    $stmt->bind_param('s', $student_email);
    $stmt->execute();
    $am_result = $stmt->get_result();

    if ($am_result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "No AM found for the given student email."]);
        $stmt->close();
        exit;
    }

    $student_am = intval($am_result->fetch_assoc()['am']);

    $query = "
        SELECT 
            dk.id,
            dk.topic AS thesis_topic,
            dk.summary AS thesis_summary,
            dk.status AS thesis_status,
            dk.file_name AS thesis_file,
            dk.start_date AS thesis_start_date,
            CONCAT(u_supervisor.name, ' ', u_supervisor.surname) AS supervisor_name,
            CONCAT(u1.name, ' ', u1.surname) AS member1,
            CONCAT(u2.name, ' ', u2.surname) AS member2,
            df.date_file_uploaded,
            df.nemertes_link,
            df.file_name AS thesis_student_file
        FROM 
            diplwmatiki_ka8igita dk
        INNER JOIN 
            diplwmatiki_foitita df ON dk.id = df.id_diplwmatikis
        LEFT JOIN 
            professor supervisor ON supervisor.email = dk.email
        LEFT JOIN 
            User u_supervisor ON supervisor.email = u_supervisor.email
        LEFT JOIN 
            professor p1 ON p1.email = df.prof2
        LEFT JOIN 
            professor p2 ON p2.email = df.prof3
        LEFT JOIN 
            User u1 ON p1.email = u1.email
        LEFT JOIN 
            User u2 ON p2.email = u2.email
        WHERE 
            df.am_foititi = ? AND dk.status != 'akurwmeni'
        GROUP BY 
            dk.id;
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $student_am);
    $stmt->execute();
    $result = $stmt->get_result();

    $theses = [];
    while ($row = $result->fetch_assoc()) {
        $theses[] = [
            "id" => $row['id'],
            "topic" => $row['thesis_topic'],
            "summary" => $row['thesis_summary'],
            "status" => $row['thesis_status'],
            "proff_file" => $row['thesis_file'] ? $row['thesis_file'] : null,
            "start_date" => $row['thesis_start_date'],
            "supervisor" => $row['supervisor_name'] ?? 'No supervisor assigned',
            "member1" => $row['member1'] ?? 'No member assigned',
            "member2" => $row['member2'] ?? 'No member assigned',
            "date_file_uploaded" => $row['date_file_uploaded'] ?? null,
            "nemertes_link" => $row['nemertes_link'],
            "student_file" => $row['thesis_student_file']
        ];
    }

    if (!empty($theses)) {
        echo json_encode(["success" => true, "data" => $theses]);
    } else {
        echo json_encode(["success" => false, "message" => "No theses found for the given student."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $conn->close();
}
?>