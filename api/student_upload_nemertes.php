<?php
include('../log-in-system/user_auth.php');
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $thesisId = $input['thesis_id'] ?? null;
    $repositoryLink = $input['repository_link'] ?? null;

    if (!$thesisId) {
        throw new Exception('Λείπει το id της διπλωματικής. Παρακαλώ ελέγξτε το αίτημα.');
    }

    if (!$repositoryLink) {
        throw new Exception('Λείπει το λινκ. Παρακαλώ ελέγξτε το αίτημα.');
    }

    $conn->begin_transaction();

    $updateDiplwmatikiFoititaQuery = "
        UPDATE diplwmatiki_foitita
        SET nemertes_link = ?
        WHERE id_diplwmatikis = ? AND status = 'energi'
    ";

    $stmt = $conn->prepare($updateDiplwmatikiFoititaQuery);
    $stmt->bind_param('si', $repositoryLink, $thesisId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Αποτυχία αποθήκευσης. Η ανάθεση του θέματος δεν σας ανήκει.');
    }

    $updateGramateiaQuery = "
        UPDATE gramateia
        SET nemertes_link = ?
        WHERE id_diplwmatikis = ? AND aithsh_gia = 'pros_egrisi_oloklirwmenh'
    ";

    $stmt = $conn->prepare($updateGramateiaQuery);
    $stmt->bind_param('si', $repositoryLink, $thesisId);
    $stmt->execute();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Το λινκ αποθηκεύτηκε επιτυχώς.']);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
