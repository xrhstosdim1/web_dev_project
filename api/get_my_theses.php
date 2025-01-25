<?php
include '../database/db_conf.php';
include '../log-in-system/user_auth.php';

header('Content-Type: application/json; charset=utf-8');

$email = $_SESSION['email'];

$reason = isset($_GET['reason']) ? $_GET['reason'] : null;

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$role = isset($_GET['role']) ? $_GET['role'] : 'all';

if ($reason === 'edit') {
    $query = "
        SELECT 
            dk.id, 
            dk.topic, 
            dk.start_date, 
            dk.exam_date, 
            dk.status, 
            dk.summary, 
            'Supervisor' AS role,
            CONCAT(u.name, ' ', u.surname) AS student_name
        FROM 
            diplwmatiki_ka8igita dk
        LEFT JOIN 
            diplwmatiki_foitita df ON dk.id = df.id_diplwmatikis
        LEFT JOIN 
            Students s ON df.am_foititi = s.am
        LEFT JOIN 
            User u ON s.email = u.email
        WHERE 
            dk.email = ? AND (dk.status = 'diathesimi' OR dk.status = 'pros_anathesi')
        ORDER BY dk.id DESC
    ";

    $params = [$email];
} else {
    if ($role === 'supervisor') {
        $query = "
            SELECT 
                dk.id, 
                dk.topic, 
                dk.start_date, 
                dk.exam_date, 
                dk.status, 
                dk.summary, 
                'Supervisor' AS role,
                CONCAT(u.name, ' ', u.surname) AS student_name
            FROM 
                diplwmatiki_ka8igita dk
            LEFT JOIN 
                diplwmatiki_foitita df ON dk.id = df.id_diplwmatikis
            LEFT JOIN 
                Students s ON df.am_foititi = s.am
            LEFT JOIN 
                User u ON s.email = u.email
            WHERE 
                dk.email = ?
        ";

        if ($status !== 'all') {
            $query .= " AND dk.status = ?";
        }

        $query .= " ORDER BY dk.id DESC";

        $params = [$email];
        if ($status !== 'all') {
            $params[] = $status;
        }
    } elseif ($role === 'member') {
        $query = "
            SELECT 
                df.id_diplwmatikis AS id, 
                dk.topic, 
                dk.start_date, 
                dk.exam_date, 
                dk.status, 
                dk.summary, 
                'Member' AS role,
                CONCAT(u.name, ' ', u.surname) AS student_name
            FROM 
                diplwmatiki_foitita df
            JOIN 
                diplwmatiki_ka8igita dk ON df.id_diplwmatikis = dk.id
            LEFT JOIN 
                Students s ON df.am_foititi = s.am
            LEFT JOIN 
                User u ON s.email = u.email
            WHERE 
                (df.prof2 = ? OR df.prof3 = ?)
        ";

        if ($status !== 'all') {
            $query .= " AND dk.status = ?";
        }

        $query .= " ORDER BY dk.id DESC";

        $params = [$email, $email];
        if ($status !== 'all') {
            $params[] = $status;
        }
    } else {
        $query = "
            SELECT 
                dk.id, 
                dk.topic, 
                dk.start_date, 
                dk.exam_date, 
                dk.status, 
                dk.summary, 
                'Supervisor' AS role,
                CONCAT(u.name, ' ', u.surname) AS student_name
            FROM 
                diplwmatiki_ka8igita dk
            LEFT JOIN 
                diplwmatiki_foitita df ON dk.id = df.id_diplwmatikis
            LEFT JOIN 
                Students s ON df.am_foititi = s.am
            LEFT JOIN 
                User u ON s.email = u.email
            WHERE 
                dk.email = ?
        ";

        if ($status !== 'all') {
            $query .= " AND dk.status = ?";
        }

        $query .= "
            UNION ALL
            SELECT 
                df.id_diplwmatikis AS id, 
                dk.topic, 
                dk.start_date, 
                dk.exam_date, 
                dk.status, 
                dk.summary, 
                'Member' AS role,
                CONCAT(u.name, ' ', u.surname) AS student_name
            FROM 
                diplwmatiki_foitita df
            JOIN 
                diplwmatiki_ka8igita dk ON df.id_diplwmatikis = dk.id
            LEFT JOIN 
                Students s ON df.am_foititi = s.am
            LEFT JOIN 
                User u ON s.email = u.email
            WHERE 
                (df.prof2 = ? OR df.prof3 = ?)
        ";

        if ($status !== 'all') {
            $query .= " AND dk.status = ?";
        }

        $query .= " ORDER BY id DESC";

        $params = [$email];
        if ($status !== 'all') {
            $params[] = $status;
        }
        $params[] = $email;
        $params[] = $email;
        if ($status !== 'all') {
            $params[] = $status;
        }
    }
}

$stmt = $conn->prepare($query);

$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $theses[] = [
        'id' => $row['id'],
        'topic' => $row['topic'],
        'start_date' => $row['start_date'],
        'exam_date' => $row['exam_date'],
        'status' => $row['status'],
        'summary' => $row['summary'],
        'role' => $row['role'],
        'student' => $row['student_name'] ?? 'N/A'
    ];
}

echo json_encode(['success' => true, 'theses' => $theses]);

$stmt->close();
$conn->close();
?>