<?php
include('../database/db_conf.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $thesisId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $currentUserEmail = $_SESSION['email'] ?? null;

    if ($thesisId === 0 || !$currentUserEmail) {
        throw new Exception('Invalid thesis ID.');
    }

    $query = "
        SELECT 
            dk.id AS thesis_id,
            dk.topic AS thesis_topic,
            dk.summary AS thesis_summary,
            DATE_FORMAT(dk.creation_date, '%d/%m/%Y') AS creation_date,
            dk.status,
            dk.start_date,
            DATE_FORMAT(dk.start_date, '%d/%m/%Y') AS start_date_formatted,
            dk.exam_date AS raw_exam_date,
            DATE_FORMAT(dk.exam_date, '%d/%m/%Y %H:%i') AS exam_date,
            dk.completion_date,
            DATE_FORMAT(dk.completion_date, '%d/%m/%Y') AS completion_date,
            dk.file_name,
            dk.email AS supervisor_email,
            u_prof.name AS supervisor_name,
            u_prof.surname AS supervisor_surname
        FROM 
            diplwmatiki_ka8igita dk
        LEFT JOIN 
            professor p ON dk.email = p.email
        LEFT JOIN 
            User u_prof ON p.email = u_prof.email
        WHERE 
            dk.id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Thesis not found.');
    }

    $coreDetails = $result->fetch_assoc();

    $twoYearsPassed = false;
    if (!empty($coreDetails['start_date'])) {
        $startDate = new DateTime($coreDetails['start_date']);
        $currentDate = new DateTime();
        $interval = $startDate->diff($currentDate);

        if ($interval->y >= 2) {
            $twoYearsPassed = true;
        }
    }

    //check an perase h hmeromhnia eksetashs
    $examDatePass = false;
    if (!empty($coreDetails['raw_exam_date'])) {
        $examDate = new DateTime($coreDetails['raw_exam_date']);
        $currentDate = new DateTime();

        if ($examDate <= $currentDate) {
            $examDatePass = true;
        }
    }

    $isSupervisor = ($coreDetails['supervisor_email'] === $currentUserEmail);

    $studentQuery = "
        SELECT 
            df.am_foititi,
            DATE_FORMAT(df.date_selected, '%d/%m/%Y') AS selection_date,
            u_student.name AS student_name,
            u_student.surname AS student_surname
        FROM 
            diplwmatiki_foitita df
        LEFT JOIN 
            Students s ON df.am_foititi = s.am
        LEFT JOIN 
            User u_student ON s.email = u_student.email
        WHERE 
            df.id_diplwmatikis = ?
    ";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    $studentDetails = $result->fetch_assoc();

    $membersQuery = "
        SELECT 
            (SELECT CONCAT(u.name, ' ', u.surname)
             FROM professor p2
             LEFT JOIN User u ON p2.email = u.email
             WHERE p2.email = df.prof2) AS member2,
            (SELECT CONCAT(u.name, ' ', u.surname)
             FROM professor p3
             LEFT JOIN User u ON p3.email = u.email
             WHERE p3.email = df.prof3) AS member3
        FROM 
            diplwmatiki_foitita df
        WHERE 
            df.id_diplwmatikis = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($membersQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = $result->fetch_assoc();

    $requestedDateQuery = "
        SELECT DATE_FORMAT(date_requested, '%d/%m/%Y') AS thesis_requested
        FROM gramateia
        LIMIT 1
    ";
    $stmt = $conn->prepare($requestedDateQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    $requestedDetails = $result->fetch_assoc();

    $response = [
        'success' => true,
        'details' => [
            'topic'           => $coreDetails['thesis_topic'] ?? 'N/A',
            'summary'         => $coreDetails['thesis_summary'] ?? 'N/A',
            'creation_date'   => $coreDetails['creation_date'] ?? 'N/A',
            'status'          => $coreDetails['status'] ?? 'N/A',
            'start_date'      => $coreDetails['start_date_formatted'] ?? 'N/A',
            'exam_date'       => $coreDetails['exam_date'] ?? 'N/A',
            'exam_date_pass'  => $examDatePass,
            'file_name'       => $coreDetails['file_name'] ?? 'N/A',
            'supervisor'      => $coreDetails['supervisor_name'] . ' ' . $coreDetails['supervisor_surname'],
            'student'         => $studentDetails
                ? $studentDetails['student_name'] . ' ' . $studentDetails['student_surname']
                : 'N/A',
            'selection_date'  => $studentDetails['selection_date'] ?? 'N/A',
            'member2'         => $members['member2'] ?? 'N/A',
            'member3'         => $members['member3'] ?? 'N/A',
            'thesis_requested' => $requestedDetails['thesis_requested'] ?? 'N/A',
            'is_supervisor'   => $isSupervisor,
            'two_years_passed' => $twoYearsPassed,
            'completion_date' => $coreDetails['completion_date'] ?? 'N/A'
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
