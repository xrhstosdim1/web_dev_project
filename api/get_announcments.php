<?php
require_once '../database/db_conf.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    $query = "
        SELECT 
            CONCAT(u.name, ' ', u.surname) AS student_name,
            d.topic AS thesis_topic,
            a.exam_date,
            a._location,
            a.ann_body
        FROM announcments a
        JOIN diplwmatiki_foitita df ON a.id_diplwmatikis = df.id_diplwmatikis
        JOIN Students s ON df.am_foititi = s.am
        JOIN User u ON s.email = u.email
        JOIN diplwmatiki_ka8igita d ON a.id_diplwmatikis = d.id
        WHERE a.status='public'
    ";

    if ($start_date) {
        $query .= " AND a.exam_date >= ?";
    }
    if ($end_date) {
        $query .= " AND a.exam_date <= ?";
    }

    $query .= " ORDER BY a.exam_date ASC";

    $stmt = $conn->prepare($query);

    $params = [];
    if ($start_date) $params[] = $start_date;
    if ($end_date) $params[] = $end_date;
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }

    echo json_encode(['success' => true, 'announcements' => $announcements]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ανάκτηση των ανακοινώσεων.', 'error' => $e->getMessage()]);
}
?>