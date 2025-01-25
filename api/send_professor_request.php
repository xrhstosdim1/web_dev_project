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
$professorEmail = $input['professorEmail'] ?? null;
$thesisID = $input['thesisID'] ?? null;

if (!$professorEmail) {
    echo json_encode(['success' => false, 'message' => 'Λείπει το email του καθηγητή.']);
    exit();
}

if (!$thesisID) {
    echo json_encode(['success' => false, 'message' => 'Λείπει ID διπλωματικής.']);
    exit();
}

$conn->begin_transaction();

try {

    // check an to ksefteri o foithths paei na steilei prosklisi ston epivleponta 
    $checkProfessorEmailStmt = $conn->prepare("
        SELECT email 
        FROM diplwmatiki_ka8igita 
        WHERE id = ?
    ");
    $checkProfessorEmailStmt->bind_param('i', $thesisID);
    $checkProfessorEmailStmt->execute();
    $professorResult = $checkProfessorEmailStmt->get_result();

    $professorRow = $professorResult->fetch_assoc();
    if (strtolower(trim($professorRow['email'])) === strtolower(trim($professorEmail))) {
        throw new Exception('Δεν μπορείτε να στείλετε πρόσκληση στον ήδη ανατεθειμένο καθηγητή της διπλωματικής.');
    }
    $checkProfessorEmailStmt->close();


    // check an exei steilei hdh request ston kathigiti
    $checkRequestStmt = $conn->prepare("
        SELECT status FROM epivlepontes_requests 
        WHERE student_am = ? AND id_diplomatikis = ? AND prof_email = ?
    ");
    $checkRequestStmt->bind_param('iis', $studentAM, $thesisID, $professorEmail);
    $checkRequestStmt->execute();
    $requestResult = $checkRequestStmt->get_result();

    if ($requestResult->num_rows > 0) {
        $existingRequest = $requestResult->fetch_assoc();
        $status = $existingRequest['status'];
        if ($status === 'accepted') {
            throw new Exception('Έχετε στείλει ήδη πρόσκληση στον καθηγητή και σας έχει αποδεχτεί.');
        } elseif ($status === 'rejected') {
            throw new Exception('Έχετε στείλει ήδη πρόσκληση στον καθηγητή και σας έχει απορρίψει.');
        } elseif ($status === 'canceled') {
            throw new Exception('Έχετε στείλει ήδη πρόσκληση στον καθηγητή και έχει απορριφθεί αυτόματα λόγω συμπλήρωσης των θέσεων.');
        }else {
            throw new Exception('Έχετε ήδη αποστείλει πρόσκληση στον καθηγητή. Παρακαλώ περιμένετε απάντηση.');
        }
    }
    $checkRequestStmt->close();


    // check an exei hdh 2 accepted
    $checkAcceptedStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM epivlepontes_requests 
        WHERE student_am = ? AND id_diplomatikis = ? AND status = 'accepted'
    ");
    $checkAcceptedStmt->bind_param('ii', $studentAM, $thesisID);
    $checkAcceptedStmt->execute();
    $acceptedResult = $checkAcceptedStmt->get_result();
    $acceptedRow = $acceptedResult->fetch_assoc();
    $checkAcceptedStmt->close();

    if ($acceptedRow['count'] >= 2) {
        throw new Exception('Σας έχουν αποδεχτεί ήδη 2 καθηγητές. Θα ενημερωθείτε από τη γραμματεία για την κατάσταση της διπλωματικής σας.');
    }

    
    // insert to request
    $stmt = $conn->prepare("
        INSERT INTO epivlepontes_requests (id_diplomatikis, student_am, prof_email, status) 
        VALUES (?, ?, ?, 'pending')
    ");
    $stmt->bind_param('iis', $thesisID, $studentAM, $professorEmail);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Η πρόσκληση στάλθηκε επιτυχώς.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>