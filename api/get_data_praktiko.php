<?php
include('../database/db_conf.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $thesisId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($thesisId === 0) {
        throw new Exception('Invalid thesis ID.');
    }
    $query = "
        SELECT 
            df.am_foititi,
            df.prof2,
            df.prof3,
            dk.email AS supervisor_email,
            dk.topic AS thesis_topic
        FROM 
            diplwmatiki_foitita df
        INNER JOIN 
            diplwmatiki_ka8igita dk ON df.id_diplwmatikis = dk.id
        WHERE 
            df.id_diplwmatikis = ? AND df.status = 'energi'
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('de vrethike energi diplwmatiki me auto to id.');
    }

    $details = $result->fetch_assoc();
    $studentAm = $details['am_foititi'];
    $prof2Email = $details['prof2'];
    $prof3Email = $details['prof3'];
    $supervisorEmail = $details['supervisor_email'];
    $thesisTopic = $details['thesis_topic'];

    $studentQuery = "
        SELECT 
            u.name AS student_name,
            u.surname AS student_surname
        FROM 
            Students s
        INNER JOIN 
            User u ON s.email = u.email
        WHERE 
            s.am = ?
    ";

    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param('i', $studentAm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('De vrethike foithths.');
    }

    $student = $result->fetch_assoc();

    $professorsQuery = "
        SELECT 
            p.email,
            u.name,
            u.surname
        FROM 
            professor p
        INNER JOIN 
            User u ON p.email = u.email
        WHERE 
            p.email IN (?, ?, ?)
    ";

    $stmt = $conn->prepare($professorsQuery);
    $stmt->bind_param('sss', $supervisorEmail, $prof2Email, $prof3Email);
    $stmt->execute();
    $result = $stmt->get_result();

    $professors = [];
    while ($row = $result->fetch_assoc()) {
        $professors[$row['email']] = $row['name'] . ' ' . $row['surname'];
    }

    $gradesQuery = "
        SELECT 
            prof1_final_grade,
            prof2_final_grade,
            prof3_final_grade,
            final_grade
        FROM 
            vathmologio
        WHERE 
            id_diplwmatikis = ? AND (status = 'egkekrimeni' OR status = 'anamoni_gia_egkrisi')
    ";

    $stmt = $conn->prepare($gradesQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Grades not found.');
    }

    $grades = $result->fetch_assoc();

    $examQuery = "
        SELECT 
            exam_date,
            _location
        FROM 
            announcments
        WHERE 
            id_diplwmatikis = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($examQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    $exam = $result->fetch_assoc();

    $protocolQuery = "
        SELECT 
            ari8mos_protokolou
        FROM 
            gramateia
        WHERE 
            id_diplwmatikis = ? AND am_foititi = ? AND aithsh_gia = 'pros_egrisi_energi'
        LIMIT 1
    ";

    $stmt = $conn->prepare($protocolQuery);
    $stmt->bind_param('ii', $thesisId, $studentAm);
    $stmt->execute();
    $result = $stmt->get_result();

    $protocol = $result->fetch_assoc();

    $response = [
        'success' => true,
        'details' => [
            'student_name' => $student['student_name'] . ' ' . $student['student_surname'],
            'topic' => $thesisTopic ?? 'N/A',
            'supervisor' => $professors[$supervisorEmail] ?? 'N/A',
            'member1' => $professors[$prof2Email] ?? 'N/A',
            'member2' => $professors[$prof3Email] ?? 'N/A',
            'prof1_final_grade' => $grades['prof1_final_grade'] ?? 'N/A',
            'prof2_final_grade' => $grades['prof2_final_grade'] ?? 'N/A',
            'prof3_final_grade' => $grades['prof3_final_grade'] ?? 'N/A',
            'final_grade' => $grades['final_grade'] ?? 'N/A',
            'exam_date' => $exam['exam_date'] ?? 'N/A',
            'location' => $exam['_location'] ?? 'N/A',
            'ar_prot' => $protocol['ari8mos_protokolou'] ?? 'N/A'
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
