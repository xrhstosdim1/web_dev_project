<?php
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['requestId'], $data['action'])) {
        throw new Exception('Λείπουν απαραίτητα δεδομένα.');
    }

    $requestId = $data['requestId'];
    $action = $data['action'];

    // check an exei ginei hdh accept
    $checkAcceptedQuery = "
        SELECT status 
        FROM epivlepontes_requests
        WHERE id = ? AND status = 'accepted'
    ";
    $stmt = $conn->prepare($checkAcceptedQuery);
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Η πρόσκληση έχει γίνει ήδη αποδεκτή.');
    }

    // update if not
    $updateRequestQuery = "
        UPDATE epivlepontes_requests
        SET status = ?, date_answered = NOW()
        WHERE id = ?
    ";
    $stmt = $conn->prepare($updateRequestQuery);
    $stmt->bind_param('si', $action, $requestId);

    if (!$stmt->execute()) {
        throw new Exception('Σφάλμα κατά την ενημέρωση του αιτήματος.');
    }

    if ($action === 'accepted') {
        $getRequestQuery = "
            SELECT student_am, id_diplomatikis, prof_email
            FROM epivlepontes_requests
            WHERE id = ?
        ";
        $stmt = $conn->prepare($getRequestQuery);
        $stmt->bind_param('i', $requestId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Το αίτημα δεν βρέθηκε.');
        }

        $request = $result->fetch_assoc();
        $studentAM = $request['student_am'];
        $diplomaticID = $request['id_diplomatikis'];
        $professorEmail = $request['prof_email'];

        $checkProfQuery = "
            SELECT prof2, prof3
            FROM diplwmatiki_foitita
            WHERE am_foititi = ? AND id_diplwmatikis = ?
        ";
        $stmt = $conn->prepare($checkProfQuery);
        $stmt->bind_param('ii', $studentAM, $diplomaticID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Δεν βρέθηκε εγγραφή διπλωματικής.');
        }

        $professors = $result->fetch_assoc();
        if (empty($professors['prof2'])) {
            $updateProfQuery = "
                UPDATE diplwmatiki_foitita
                SET prof2 = ?
                WHERE am_foititi = ? AND id_diplwmatikis = ?
            ";
        } elseif (empty($professors['prof3'])) {
            $updateProfQuery = "
                UPDATE diplwmatiki_foitita
                SET prof3 = ?
                WHERE am_foititi = ? AND id_diplwmatikis = ?
            ";
        } else {
            $rejectRequestQuery = "
                UPDATE epivlepontes_requests
                SET status = 'cancelled', date_answered = NOW()
                WHERE id = ?
            ";
            $stmt = $conn->prepare($rejectRequestQuery);
            $stmt->bind_param('i', $requestId);
            $stmt->execute();
            throw new Exception('Ο φοιτητής έχει ήδη δύο διδάσκοντες. Η πρόσκληση απορρίφθηκε.');
        }

        $stmt = $conn->prepare($updateProfQuery);
        $stmt->bind_param('sii', $professorEmail, $studentAM, $diplomaticID);

        if (!$stmt->execute()) {
            throw new Exception('Σφάλμα κατά την ενημέρωση του καθηγητή στη διπλωματική.');
        }

        $callProcedureQuery = "CALL update_diplwmatiki_procedure(?)";
        $stmt = $conn->prepare($callProcedureQuery);
        $stmt->bind_param('i', $diplomaticID);

        if (!$stmt->execute()) {
            throw new Exception('Σφάλμα κατά την κλήση της procedure update_diplwmatiki_procedure.');
        }
    }

    echo json_encode(['success' => true, 'message' => 'Η ενέργεια ολοκληρώθηκε επιτυχώς.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>