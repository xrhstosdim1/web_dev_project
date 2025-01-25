<?php
include('../../log-in-system/user_auth.php');

header('Content-Type: application/json; charset=utf-8');

try {
    include('../../database/db_conf.php');

    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $reason = isset($_GET['reason']) ? $_GET['reason'] : 'all';

    $query = "
        SELECT 
            g.id,
            u2.name AS applicant_name,
            u2.surname AS applicant_surname,
            g.id_diplwmatikis,
            g.date_requested,
            g.aithsh_gia,
            g.apanthsh
        FROM gramateia g
        JOIN User u2 ON g.aitwn_email = u2.email
        WHERE 1=1
    ";

    $params = [];
    $types = '';

    if ($status !== 'all') {
        $query .= " AND g.apanthsh = ?";
        $params[] = $status;
        $types .= 's';
    }

    if ($reason !== 'all') {
        $query .= " AND g.aithsh_gia = ?";
        $params[] = $reason;
        $types .= 's';
    }

    $query .= " ORDER BY g.date_requested DESC";

    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $gramateia = [];
        while ($row = $result->fetch_assoc()) {
            $gramateia[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $gramateia]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκαν δεδομένα.']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>