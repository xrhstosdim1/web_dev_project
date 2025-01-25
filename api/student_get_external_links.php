<?php
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_diplwmatikis'])) {
        throw new Exception('Το ID της διπλωματικής δεν έχει καθοριστεί.');
    }

    $idDiplwmatikis = intval($data['id_diplwmatikis']);

    $stmt = $conn->prepare("SELECT link FROM diplwmatiki_links WHERE id_diplwmatikis = ?");
    $stmt->bind_param("i", $idDiplwmatikis);
    $stmt->execute();
    $result = $stmt->get_result();

    $links = [];
    while ($row = $result->fetch_assoc()) {
        $links[] = $row['link'];
    }

    echo json_encode(['success' => true, 'links' => $links]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>