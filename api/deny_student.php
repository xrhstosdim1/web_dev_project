<?php
include('../database/db_conf.php');
session_start();
//o kathigitis diwxnei ton foithth prin ginei pros egkrisi apo to koumpi tou 
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $thesisId = isset($data['thesisId']) ? intval($data['thesisId']) : 0;

    if (!$thesisId) {
        throw new Exception('Invalid thesis ID.');
    }

    $supervisorEmail = $_SESSION['email'];
    $checkSupervisorQuery = "SELECT 1 FROM diplwmatiki_ka8igita WHERE id = ? AND email = ?";
    $stmt = $conn->prepare($checkSupervisorQuery);
    $stmt->bind_param('is', $thesisId, $supervisorEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Δεν έχετε δικαίωμα να απορρίψετε αυτή τη διπλωματική.');
    }

    $deleteQuery = "DELETE FROM diplwmatiki_foitita WHERE id_diplwmatikis = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Αποτυχία διαγραφής της εγγραφής από τον πίνακα φοιτητών.');
    }

    $updateQuery = "UPDATE diplwmatiki_ka8igita SET status = 'diathesimi' WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Αποτυχία ενημέρωσης του status της διπλωματικής.');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>