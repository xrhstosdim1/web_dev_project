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

    if (!preg_match('/^https?:\/\//i', $link)) {
        $link = 'https://' . $link;
    }

    //validation tou link oti einai link kai oxi trash
    if (empty($link) || !filter_var($link, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'Ο σύνδεσμος δεν είναι έγκυρος.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO diplwmatiki_links (id_diplwmatikis, link) VALUES (?, ?)");
    $stmt->bind_param("is", $idDiplwmatikis, $link);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Ο σύνδεσμος αποθηκεύτηκε επιτυχώς.']);
    } else {
        throw new Exception('Σφάλμα κατά την αποθήκευση του συνδέσμου.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>