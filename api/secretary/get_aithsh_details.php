<?php
include('../../database/db_conf.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($requestId === 0) {
        throw new Exception('Invalid request ID.');
    }

    $query = "
        SELECT 
            g.id AS request_id,
            g.id_diplwmatikis AS thesis_id,
            g.am_foititi AS student_am,
            g.date_requested AS request_date,
            g.aithsh_gia AS request_reason,
            g.apanthsh AS request_status,
            g.comment,
            g.ari8mos_protokolou AS protocol_number,
            DATE_FORMAT(g.date_of_response, '%d/%m/%Y') AS response_date
        FROM 
            gramateia g
        WHERE 
            g.id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Request not found.');
    }

    $coreDetails = $result->fetch_assoc();

    $studentQuery = "
        SELECT 
            u.name AS student_name,
            u.surname AS student_surname
        FROM 
            Students s
        LEFT JOIN 
            User u ON s.email = u.email
        WHERE 
            s.am = ?
    ";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param('i', $coreDetails['student_am']);
    $stmt->execute();
    $result = $stmt->get_result();

    $studentDetails = $result->fetch_assoc();

    $membersQuery = "
        SELECT 
            (SELECT CONCAT(u1.name, ' ', u1.surname)
             FROM User u1
             WHERE u1.email = g.prof1) AS supervisor,
            (SELECT CONCAT(u2.name, ' ', u2.surname)
             FROM User u2
             WHERE u2.email = g.prof2) AS member2,
            (SELECT CONCAT(u3.name, ' ', u3.surname)
             FROM User u3
             WHERE u3.email = g.prof3) AS member3
        FROM 
            gramateia g
        WHERE 
            g.id = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($membersQuery);
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = $result->fetch_assoc();

    $thesisQuery = "
        SELECT 
            dk.topic AS thesis_topic,
            dk.summary AS thesis_summary,
            DATE_FORMAT(dk.creation_date, '%d/%m/%Y') AS creation_date,
            dk.file_name
        FROM 
            diplwmatiki_ka8igita dk
        WHERE 
            dk.id = ?
    ";
    $stmt = $conn->prepare($thesisQuery);
    $stmt->bind_param('i', $coreDetails['thesis_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $thesisDetails = $result->fetch_assoc();

    $response = [
        'success' => true,
        'details' => [
            'request_id'      => $coreDetails['request_id'],
            'request_date'    => $coreDetails['request_date'] ?? 'N/A',
            'request_reason'  => $coreDetails['request_reason'] ?? 'N/A',
            'request_status'  => $coreDetails['request_status'] ?? 'N/A',
            'protocol_number' => $coreDetails['protocol_number'] ?? 'N/A',
            'comment'         => $coreDetails['comment'] ?? 'N/A',
            'response_date'   => $coreDetails['response_date'] ?? 'N/A',
            'student_am'      => $coreDetails['student_am'] ?? 'N/A',
            'student'         => $studentDetails
                ? $studentDetails['student_name'] . ' ' . $studentDetails['student_surname']
                : 'N/A',
            'supervisor'      => $members['supervisor'] ?? 'N/A',
            'member2'         => $members['member2'] ?? 'N/A',
            'member3'         => $members['member3'] ?? 'N/A',
            'thesis'          => $thesisDetails
                ? [
                    'id_dipl' => $coreDetails['thesis_id'] ?? 'N/A',
                    'topic'         => $thesisDetails['thesis_topic'] ?? 'N/A',
                    'summary'       => $thesisDetails['thesis_summary'] ?? 'N/A',
                    'creation_date' => $thesisDetails['creation_date'] ?? 'N/A',
                    'file_name'     => $thesisDetails['file_name'] ?? 'N/A'
                ]
                : 'N/A'
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}