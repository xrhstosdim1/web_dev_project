<?php
include('../database/db_conf.php');
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_GET['id_diplwmatikis'])) {
        echo json_encode(['success' => false, 'message' => 'Δεν δώθηκε id διπλωματικής.']);
        exit;
    }

    $id_diplwmatikis = intval($_GET['id_diplwmatikis']);

    $stmtLinks = $conn->prepare("SELECT link FROM diplwmatiki_links WHERE id_diplwmatikis = ?");
    $stmtLinks->bind_param('i', $id_diplwmatikis);
    $stmtLinks->execute();
    $resultLinks = $stmtLinks->get_result();
    $links = $resultLinks->fetch_all(MYSQLI_ASSOC); //ta fernei san pinaka this time broken to prohgoumeno
    $stmtLinks->close();

    $stmtAnnouncements = $conn->prepare("SELECT * FROM announcments WHERE id_diplwmatikis = ?");
    $stmtAnnouncements->bind_param('i', $id_diplwmatikis);
    $stmtAnnouncements->execute();
    $resultAnnouncements = $stmtAnnouncements->get_result();
    $announcements = $resultAnnouncements->fetch_all(MYSQLI_ASSOC);
    $stmtAnnouncements->close();

    $stmtFileName = $conn->prepare("SELECT file_name FROM diplwmatiki_foitita WHERE id_diplwmatikis = ?");
    $stmtFileName->bind_param('i', $id_diplwmatikis);
    $stmtFileName->execute();
    $resultFileName = $stmtFileName->get_result();
    $fileName = $resultFileName->fetch_assoc()['file_name'] ?? null;
    $stmtFileName->close();

    echo json_encode([
        'success' => true,
        'links' => $links,
        'announcements' => $announcements,
        'file_name' => $fileName
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
