<?php
include('../database/db_conf.php');
include('../log-in-system/user_auth.php');

header('Content-Type: application/json; charset=utf-8');

$email = $_SESSION['email'];
try {
    $diplomaId = isset($_GET['id_diplomatikis']) ? intval($_GET['id_diplomatikis']) : 0;

    if ($diplomaId === 0 || empty($email)) {
        throw new Exception('Invalid diploma ID or email.');
    }

    $query = "
        SELECT 
            comment,
            date_commented
        FROM 
            professor_comments_on_theses
        WHERE 
            id_diplomatikis = ? AND prof_email = ?
        ORDER BY
            date_commented DESC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("SQL prepare error: " . $conn->error);
    }

    $stmt->bind_param('is', $diplomaId, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'comment'        => $row['comment'],
            'date_commented' => $row['date_commented']
        ];
    }

    echo json_encode(['success' => true, 'requests' => $requests]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>