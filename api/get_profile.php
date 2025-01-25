<?php
include ("../log-in-system/user_auth.php");
include('../database/db_conf.php');

$email = $_SESSION['email'];

$stmt = $conn->prepare("
    SELECT 
        u.name AS first_name, 
        u.surname AS last_name, 
        s.am, 
        s.email,
        s.street, 
        s.str_number, 
        s.city, 
        s.postcode, 
        s.mob AS mobile_phone, 
        s.tel AS landline_phone
    FROM Students s
    INNER JOIN User u ON s.email = u.email
    WHERE s.email = ?
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo json_encode([
        'success' => true,
        'user' => [
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'am' => $user['am'],
            'email' => $user['email'],
            'street' => $user['street'],
            'str_number' => $user['str_number'],
            'city' => $user['city'],
            'postcode' => $user['postcode'],
            'mobile_phone' => $user['mobile_phone'],
            'landline_phone' => $user['landline_phone']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκαν στοιχεία χρήστη.']);
}

$stmt->close();
$conn->close();
?>