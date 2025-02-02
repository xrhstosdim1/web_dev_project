<?php
include('../../log-in-system/user_auth.php');
include('../../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $thesisId = $input['id'] ?? null;

    if (!$thesisId) {
        throw new Exception('Λείπει το ID της διπλωματικής. Παρακαλώ ελέγξτε το αίτημα.');
    }

    $reason = 'foititis';

    $stmt = $conn->prepare("CALL insert_grammateia_pros_allagh(?, ?)");
    $stmt->bind_param('is', $thesisId, $reason);

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Η αίτηση αλλαγής φοιτητή καταχωρήθηκε επιτυχώς. Εκκρεμεί απάντηση στις αιτήσεις.']);
    } else {
        throw new Exception('Den etrekse to insert_grammateia_pros_allagh');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>