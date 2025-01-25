<?php
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_diplomatikis']) || !isset($data['ann_body']) || !isset($data['status'])) {
        throw new Exception('Λείπουν απαραίτητα δεδομένα.');
    }

    $idDiplwmatikis = $data['id_diplomatikis'];
    $annBody = $data['ann_body'];
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE announcments SET ann_body = ?, status = ? WHERE id_diplwmatikis = ?");
    $stmt->bind_param('ssi', $annBody, $status, $idDiplwmatikis);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Η ανακοίνωση ενημερώθηκε επιτυχώς.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκε εγγραφή για το ID ή δεν έγιναν αλλαγές.']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
