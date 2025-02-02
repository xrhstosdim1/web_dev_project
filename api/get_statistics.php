<?php
include('../database/db_conf.php');
include('../log-in-system/user_auth.php');

header('Content-Type: application/json');

$email = $_SESSION['email'];

if (!$email) {
    echo json_encode([
        'success' => false,
        'message' => 'No prof email found.'
    ]);
    exit;
}

$response = [
    'success' => true,
    'supervisor' => [
        'avg_grade' => null,
        'total_theses' => 0,
        'avg_completion_time' => null
    ],
    'committee_member' => [
        'avg_grade' => null,
        'total_theses' => 0,
        'avg_completion_time' => null
    ]
];

try {
    //average grade epivlepon
    $query = "SELECT AVG(final_grade) AS avg_grade_supervisor FROM vathmologio WHERE prof1 = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $response['supervisor']['avg_grade'] = $data['avg_grade_supervisor'];

    //average grade melos
    $query = "SELECT AVG(final_grade) AS avg_grade_member FROM vathmologio WHERE prof2 = ? OR prof3 = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $response['committee_member']['avg_grade'] = $data['avg_grade_member'];

    //arithmos diplwmatikwn epivlepon
    $query = "SELECT COUNT(*) AS supervised_count FROM diplwmatiki_ka8igita WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $response['supervisor']['total_theses'] = $data['supervised_count'];

    //arithmos diplwmatikwn melos
    $query = "SELECT COUNT(*) AS co_supervised_count FROM diplwmatiki_foitita WHERE (prof2 = ? OR prof3 = ?) AND status = 'energi'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $response['committee_member']['total_theses'] = $data['co_supervised_count'];

    //mesos xronos oloklirwshs epivlepon
    $query = "
        SELECT AVG(DATEDIFF(completion_date, start_date)) AS avg_completion_time_supervisor 
        FROM diplwmatiki_ka8igita 
        WHERE status = 'oloklirwmeni' AND email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $response['supervisor']['avg_completion_time'] = $data['avg_completion_time_supervisor'];

    //mesos xronos oloklirwshs melos
    $query = "
        SELECT AVG(DATEDIFF(completion_date, start_date)) AS avg_completion_time_member 
        FROM diplwmatiki_ka8igita 
        WHERE status = 'oloklirwmeni' AND id IN (
            SELECT id_diplwmatikis FROM diplwmatiki_foitita WHERE prof2 = ? OR prof3 = ?
        )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $response['committee_member']['avg_completion_time'] = $data['avg_completion_time_member'];

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την εκτέλεση του API: ' . $e->getMessage()
    ]);
    exit;
}
echo json_encode($response);
?>