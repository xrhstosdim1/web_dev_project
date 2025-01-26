<?php
include('../log-in-system/user_auth.php');
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $thesisId = $input['id'] ?? null;

    if (!$thesisId) {
        throw new Exception('Λείπει το ID της διπλωματικής. Παρακαλώ ελέγξτε το αίτημα.');
    }

    $reason = 'kathigitis';

    $stmt = $conn->prepare("CALL insert_grammateia_pros_allagh(?, ?)");
    $stmt->bind_param('is', $thesisId, $reason);

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Η αίτηση αλλαγής φοιτητή καταχωρήθηκε επιτυχώς.']);
    } else {
        throw new Exception('Η αίτηση αλλαγής φοιτητή δεν καταχωρήθηκε. Ενδέχεται να υπάρχουν προβλήματα με τα δεδομένα.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>