<?php
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_diplwmatikis']) || !isset($data['link'])) {
        throw new Exception('Λείπουν απαραίτητα δεδομένα.');
    }

    $idDiplwmatikis = intval($data['id_diplwmatikis']);
    $link = trim($data['link']);

    if (empty($link)) {
        throw new Exception('Ο σύνδεσμος δεν είναι έγκυρος.');
    }

    $stmt = $conn->prepare("DELETE FROM diplwmatiki_links WHERE id_diplwmatikis = ? AND link = ?");
    $stmt->bind_param("is", $idDiplwmatikis, $link);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Ο σύνδεσμος διαγράφηκε επιτυχώς.']);
        } else {
            throw new Exception('Ο σύνδεσμος δεν βρέθηκε ή έχει ήδη διαγραφεί.');
        }
    } else {
        throw new Exception('Σφάλμα κατά τη διαγραφή του συνδέσμου.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>