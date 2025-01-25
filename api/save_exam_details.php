<?php
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

$exam_date = $data['exam_date'];
$exam_location = $data['exam_location'];
$thesis_id = $data['thesis_id'];
$student_id = $data['student_id'];

try {
    $stmt = $conn->prepare("
        INSERT INTO announcments (am_foititi, id_diplwmatikis, exam_date, _location, ann_body, status)
        VALUES (?, ?, ?, ?, '', 'private')
    ");
    $stmt->bind_param("iiss", $student_id, $thesis_id, $exam_date, $exam_location);
    $stmt->execute();

    $stmt2 = $conn->prepare("
        UPDATE diplwmatiki_ka8igita 
        SET exam_date = ? 
        WHERE id = ?
    ");
    $stmt2->bind_param("si", $exam_date, $thesis_id);
    $stmt2->execute();

    echo json_encode(['success' => true, 'message' => 'Τα στοιχεία αποθηκεύτηκαν επιτυχώς']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
