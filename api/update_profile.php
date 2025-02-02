<?php
include ("../log-in-system/user_auth.php");
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $email = $_SESSION['email'];

    $fields = [];
    $params = [];
    $types = '';

    if (isset($_POST['street'])) {
        $fields[] = 'street = ?';
        $params[] = trim($_POST['street']);
        $types .= 's';
    }
    if (isset($_POST['str_number'])) {
        $fields[] = 'str_number = ?';
        $params[] = trim($_POST['str_number']);
        $types .= 's';
    }
    if (isset($_POST['city'])) {
        $fields[] = 'city = ?';
        $params[] = trim($_POST['city']);
        $types .= 's';
    }
    if (isset($_POST['postcode'])) {
        $fields[] = 'postcode = ?';
        $params[] = trim($_POST['postcode']);
        $types .= 's';
    }
    if (isset($_POST['mobile-phone'])) {
        $mobilePhone = trim($_POST['mobile-phone']);
        if ($mobilePhone === '') {
            $fields[] = 'mob = NULL';
        } elseif (!preg_match('/^\d{10}$/', $mobilePhone)) {
            throw new Exception('Το κινητό πρέπει να είναι 10ψήφιο αριθμός.');
        } else {
            $fields[] = 'mob = ?';
            $params[] = $mobilePhone;
            $types .= 's';
        }
    }
    
    if (isset($_POST['landline-phone'])) {
        $landlinePhone = trim($_POST['landline-phone']);
        if ($landlinePhone === '') {
            $fields[] = 'tel = NULL';
        } elseif (!preg_match('/^\d{10}$/', $landlinePhone)) {
            throw new Exception('Το σταθερό πρέπει να είναι 10ψήφιο αριθμός.');
        } else {
            $fields[] = 'tel = ?';
            $params[] = $landlinePhone;
            $types .= 's';
        }
    }
    

    if (empty($fields)) {
        throw new Exception('Δεν υπάρχουν αλλαγές για ενημέρωση.');
    }

    $params[] = $email;
    $types .= 's';

    $query = "UPDATE Students SET " . implode(', ', $fields) . " WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Τα δεδομένα ενημερώθηκαν με επιτυχία.']);
    } else {
        throw new Exception('Σφάλμα κατά την ενημέρωση των δεδομένων.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>