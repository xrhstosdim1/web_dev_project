<?php
include('../../database/db_conf.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $request_id = $input['request_id'] ?? null;
    $protocol_number = $input['protocol_number'] ?? null;
    $comment = $input['comment'] ?? null;

    if (!$request_id || !$protocol_number) {
        throw new Exception('Λείπουν δεδομένα: Request ID ή Αριθμός Πρωτοκόλλου.');
    }

    $queryGetDiplwmatikiId = "
        SELECT id_diplwmatikis
        FROM gramateia
        WHERE id = ?
    ";

    $stmt = $conn->prepare($queryGetDiplwmatikiId);
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Δεν βρέθηκε η αίτηση.');
    }

    $row = $result->fetch_assoc();
    $diplwmatiki_id = $row['id_diplwmatikis'];

    $queryUpdateGramateia = "
        UPDATE gramateia
        SET apanthsh = 'denied', ari8mos_protokolou = ?, date_of_response = NOW(), comment = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($queryUpdateGramateia);
    $stmt->bind_param('isi', $protocol_number, $comment, $request_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Δεν βρέθηκε η αίτηση ή έχει ήδη ενημερωθεί.');
    }

    $queryUpdateKathigiti = "
        UPDATE diplwmatiki_ka8igita
        SET status = 'akurwmeni'
        WHERE id = ?
    ";

    $stmt = $conn->prepare($queryUpdateKathigiti);
    $stmt->bind_param('i', $diplwmatiki_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Δεν βρέθηκε η διπλωματική για ενημέρωση.');
    }

    $queryUpdateFoithth = "
        UPDATE diplwmatiki_foitita
        SET status = 'akurwmeni'
        WHERE id_diplwmatikis = ?
    ";

    $stmt = $conn->prepare($queryUpdateFoithth);
    $stmt->bind_param('i', $diplwmatiki_id);
    $stmt->execute();

    $queryDeleteRequests = "
        DELETE FROM epivlepontes_requests
        WHERE id_diplomatikis = ?
    ";

    $stmt = $conn->prepare($queryDeleteRequests);
    $stmt->bind_param('i', $diplwmatiki_id);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Η αίτηση απορρίφθηκε επιτυχώς.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>