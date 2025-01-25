<?php
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_FILES['upload-file']) || $_FILES['upload-file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Παρακαλώ ανεβάστε ένα αρχείο.');
    }

    $file = $_FILES['upload-file'];
    $allowedTypes = ['application/pdf'];

    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Το αρχείο πρέπει να είναι σε μορφή PDF.');
    }

    if (!isset($_POST['id_diplwmatikis'])) {
        throw new Exception('Το ID της διπλωματικής δεν έχει καθοριστεί.');
    }

    $idDiplwmatikis = intval($_POST['id_diplwmatikis']);

    $uploadDir = '../uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    if (!is_writable($uploadDir)) {
        throw new Exception('Λείπουν write permissions στον φάκελο ανεβάσματος.');
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Σφάλμα κατά το ανέβασμα του αρχείου.');
    }

    $stmt = $conn->prepare("UPDATE diplwmatiki_foitita SET file_name = ?, date_file_uploaded = NOW() WHERE id_diplwmatikis = ?");
    $stmt->bind_param("si", $fileName, $idDiplwmatikis);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Το αρχείο ανέβηκε και αποθηκεύτηκε επιτυχώς.',
            'filePath' => $uploadPath
        ]);
    } else {
        throw new Exception('Σφάλμα κατά την ενημέρωση της βάσης δεδομένων.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>