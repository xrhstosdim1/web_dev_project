<?php
include('../database/db_conf.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT password FROM User WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($password);
        $stmt->fetch();

        echo json_encode(['success' => true, 'password' => $password]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Το email δεν υπάρχει στο σύστημα.']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

echo json_encode(['success' => false, 'message' => 'Μη εξουσιοδοτημένη πρόσβαση.']);
exit();
