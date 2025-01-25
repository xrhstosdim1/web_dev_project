<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../database/db_conf.php';

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $id = $_POST['id'] ?? null;
    $topic = $_POST['topic'] ?? null;
    $summary = $_POST['summary'] ?? null;
    $studentAm = $_POST['student_am'] ?? null;

    error_log("Received ID: $id, Topic: $topic, Summary: $summary, Student AM: $studentAm");

    if (!$id || !$topic || !$summary) {
        echo json_encode(['success' => false, 'message' => 'Όλα τα πεδία είναι υποχρεωτικά.']);
        exit;
    }

    $fileName = null;

    if (!empty($_FILES['file']['name'])) {
        $uploadDir = '../uploads/';
        $uploadFile = $uploadDir . basename($_FILES['file']['name']);
        $fileType = pathinfo($uploadFile, PATHINFO_EXTENSION);
        $allowedTypes = ['pdf', 'doc', 'docx'];

        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Μη έγκυρος τύπος αρχείου.']);
            exit;
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            $fileName = basename($_FILES['file']['name']);
        }
    }

    $query = "UPDATE diplwmatiki_ka8igita 
              SET creation_date = NOW(), topic = ?, summary = ?, file_name = IFNULL(?, file_name) 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssi', $topic, $summary, $fileName, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            if (!empty($studentAm)) {
                error_log("Ελέγχουμε αν υπάρχει ήδη φοιτητής ανατεθειμένος...");

                $checkQuery = "SELECT am_foititi FROM diplwmatiki_foitita WHERE id_diplwmatikis = ?";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param('i', $id);
                $checkStmt->execute();
                $result = $checkStmt->get_result();

                if ($result->num_rows > 0) {
                    $existingStudent = $result->fetch_assoc();
                    if ($existingStudent['am_foititi'] === $studentAm) {
                        error_log("Ο φοιτητής είναι ήδη ανατεθειμένος. Δεν απαιτείται αλλαγή.");
                    } else {
                        $deleteQuery = "DELETE FROM diplwmatiki_foitita WHERE id_diplwmatikis = ?";
                        $deleteStmt = $conn->prepare($deleteQuery);
                        $deleteStmt->bind_param('i', $id);
                        $deleteStmt->execute();

                        $assignQuery = "INSERT INTO diplwmatiki_foitita (id_diplwmatikis, am_foititi) VALUES (?, ?)";
                        $assignStmt = $conn->prepare($assignQuery);
                        $assignStmt->bind_param('is', $id, $studentAm);
                        $assignStmt->execute();
                    }
                } else {
                    $assignQuery = "INSERT INTO diplwmatiki_foitita (id_diplwmatikis, am_foititi) VALUES (?, ?)";
                    $assignStmt = $conn->prepare($assignQuery);
                    $assignStmt->bind_param('is', $id, $studentAm);
                    $assignStmt->execute();
                }

                $statusQuery = "UPDATE diplwmatiki_ka8igita SET status = 'pros_anathesi' WHERE id = ?";
                $statusStmt = $conn->prepare($statusQuery);
                $statusStmt->bind_param('i', $id);
                $statusStmt->execute();
            }

            echo json_encode(['success' => true, 'message' => 'Η αποθήκευση ήταν επιτυχής.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Η αποθήκευση έγινε, αλλά δεν άλλαξε τίποτα.']);
        }
    } else {
        error_log("Σφάλμα κατά την αποθήκευση: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την αποθήκευση.', 'error' => $stmt->error]);
    }
}
?>






