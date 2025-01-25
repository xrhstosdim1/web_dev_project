<?php
include('../log-in-system/user_auth.php');
include('../database/db_conf.php');
header('Content-Type: application/json');

$studentEmail = $_SESSION['email'] ?? null;

if (!$studentEmail) {
    echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκε το email του φοιτητή στο session.']);
    exit();
}

$getStudentAMStmt = $conn->prepare("SELECT am FROM Students WHERE email = ?");
$getStudentAMStmt->bind_param('s', $studentEmail);
$getStudentAMStmt->execute();
$getStudentAMResult = $getStudentAMStmt->get_result();

if ($getStudentAMResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Ο φοιτητής δεν βρέθηκε στη βάση δεδομένων.']);
    exit();
}

$studentRow = $getStudentAMResult->fetch_assoc();
$studentAM = $studentRow['am'];
$getStudentAMStmt->close();

$input = json_decode(file_get_contents('php://input'), true);
$thesisID = $input['thesisID'] ?? null;

if (!$thesisID) {
    echo json_encode(['success' => false, 'message' => 'Λείπει το ID της διπλωματικής.']);
    exit();
}

$conn->begin_transaction();

try { //check an exei hdh diplwmatikh
    $checkStatusStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM diplwmatiki_ka8igita 
        JOIN diplwmatiki_foitita ON diplwmatiki_ka8igita.id = diplwmatiki_foitita.id_diplwmatikis
        WHERE diplwmatiki_foitita.am_foititi = ? AND diplwmatiki_ka8igita.status != 'akurwmeni'
    ");
    $checkStatusStmt->bind_param('i', $studentAM);
    $checkStatusStmt->execute();
    $statusResult = $checkStatusStmt->get_result();
    $statusRow = $statusResult->fetch_assoc();
    $checkStatusStmt->close();

    if ($statusRow['count'] > 0) {
        throw new Exception('Υπάρχει ήδη επιλεγμένο θέμα διπλωματικής.');
    }

    $checkStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM diplwmatiki_foitita 
        WHERE am_foititi = ? AND id_diplwmatikis = ?
    ");
    $checkStmt->bind_param('ii', $studentAM, $thesisID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $checkStmt->close();

    if ($row['count'] > 0) {
        throw new Exception('Έχετε ήδη επιλέξει αυτή τη διπλωματική.');
    }

    $updateStatusStmt = $conn->prepare("UPDATE diplwmatiki_ka8igita SET status = 'pros_anathesi' WHERE id = ?");
    $updateStatusStmt->bind_param('i', $thesisID);
    $updateStatusStmt->execute();
    $updateStatusStmt->close();

    $insertThesisStmt = $conn->prepare("
        INSERT INTO diplwmatiki_foitita (am_foititi, id_diplwmatikis) 
        VALUES (?, ?)
    ");
    $insertThesisStmt->bind_param('ii', $studentAM, $thesisID);
    $insertThesisStmt->execute();
    $insertThesisStmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Το θέμα επιλέχθηκε επιτυχώς.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>