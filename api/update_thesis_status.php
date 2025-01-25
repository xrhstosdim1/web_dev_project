<?php
include('../database/db_conf.php');
include('../log-in-system/user_auth.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $thesisId = isset($input['thesisId']) ? intval($input['thesisId']) : 0;
    $newStatus = isset($input['newStatus']) ? trim($input['newStatus']) : '';

    if ($thesisId === 0 || empty($newStatus)) {
        throw new Exception('Λάθος δεδομένα εισόδου.');
    }

    if ($newStatus === 'diathesimi') {
        $checkSupervisorQuery = "
            SELECT 1 
            FROM diplwmatiki_ka8igita 
            WHERE id = ? AND email = ?
        ";
        $stmt = $conn->prepare($checkSupervisorQuery);
        $stmt->bind_param('is', $thesisId, $_SESSION['email']);
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

        echo json_encode(['success' => true, 'message' => 'Η διπλωματική έγινε διαθέσιμη.']);
        exit;
    }

    $allowedStatuses = ['exetasi', 'vathmologisi'];
    if (!in_array($newStatus, $allowedStatuses)) {
        throw new Exception('Μη έγκυρη κατάσταση.');
    }

    if ($newStatus === 'exetasi'){
        $checkStatusQuery = "
        SELECT status
        FROM diplwmatiki_ka8igita
        WHERE id = ?
    ";

    $stmt = $conn->prepare($checkStatusQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    if ($row && $row['status'] === 'exetasi') {
        throw new Exception('Η διπλωματική βρίσκεται ήδη σε αυτήν την κατάσταση.');
    }


        $updateStatusQuery = "
        UPDATE diplwmatiki_ka8igita
        SET status = ?
        WHERE id = ?
        ";
        $stmt = $conn->prepare($updateStatusQuery);
        $stmt->bind_param('si', $newStatus, $thesisId);

        if (!$stmt->execute()) {
            throw new Exception('Η αλλαγή κατάστασης απέτυχε. Προσπαθήστε ξανά.');
        }
    } else if ($newStatus === 'vathmologisi') {

        $checkStatusQuery = "
        SELECT status
        FROM diplwmatiki_ka8igita
        WHERE id = ?
        ";
        
        $stmt = $conn->prepare($checkStatusQuery);
        $stmt->bind_param('i', $thesisId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $row = $result->fetch_assoc();
        
        if ($row && $row['status'] === 'vathmologisi') {
            throw new Exception('Η διπλωματική βρίσκεται ήδη σε αυτήν την κατάσταση.');
        }
    
        //elegxos hmeromhnias eksetashs
        $checkExamDateQuery = "
            SELECT exam_date
            FROM diplwmatiki_ka8igita
            WHERE id = ?
        ";
        $stmt = $conn->prepare($checkExamDateQuery);
        $stmt->bind_param('i', $thesisId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            throw new Exception('Δεν βρέθηκε η διπλωματική.');
        }
    
        $row = $result->fetch_assoc();
        $examDate = $row['exam_date'];
    
        if (is_null($examDate)) {
            throw new Exception('Η ημερομηνία εξέτασης δεν έχει οριστεί. Δεν μπορείτε να αλλάξετε την κατάσταση σε "Βαθμολόγηση".');
        }
    
        if (strtotime($examDate) > time()) {
            throw new Exception('Η ημερομηνία εξέτασης δεν έχει περάσει ακόμα. Δεν μπορείτε να αλλάξετε την κατάσταση σε "Βαθμολόγηση".');
        }
    
        //elegxos anakoinwshs
        $checkExamDateQuery = "
            SELECT ann_body
            FROM announcments
            WHERE id_diplwmatikis = ? AND status = 'public'
            ";
        $stmt = $conn->prepare($checkExamDateQuery);
        $stmt->bind_param('i', $thesisId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Δεν έχετε ανακοινώσει ακόμη την εξέταση. Δεν μπορείτε να αλλάξετε την κατάσταση σε "Βαθμολόγηση".');
        }

        //elegxos oti o foithths exei anevasei proxeiro
        $checkStudentFileQuery = "
            SELECT file_name
            FROM diplwmatiki_foitita
            WHERE id_diplwmatikis = ? AND status = 'energi'
            ";
        $stmt = $conn->prepare($checkStudentFileQuery);
        $stmt->bind_param('i', $thesisId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            throw new Exception('Ο φοιτητής ακόμη δεν έχει ανεβάσει πρόχειρο κείμενο. Δεν μπορείτε να αλλάξετε την κατάσταση σε "Βαθμολόγηση".');
        }

        //update status
        $updateProfStatusQuery = "
            UPDATE diplwmatiki_ka8igita
            SET status = ?
            WHERE id = ?
        ";
        $stmt = $conn->prepare($updateProfStatusQuery);
        $stmt->bind_param('si', $newStatus, $thesisId);
    
        if (!$stmt->execute()) {
            throw new Exception('Η αλλαγή κατάστασης απέτυχε. Προσπαθήστε ξανά.');
        }

        //update status
        $updateVathmStatusQuery = "
            UPDATE vathmologio
            SET status = 'prosva8mologisi'
            WHERE id_diplwmatikis = ?
        ";
        $stmt = $conn->prepare($updateVathmStatusQuery);
        $stmt->bind_param('i',$thesisId);
        
        if (!$stmt->execute()) {
            throw new Exception('Η αλλαγή κατάστασης απέτυχε. Προσπαθήστε ξανά.');
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Η κατάσταση της διπλωματικής άλλαξε επιτυχώς.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>