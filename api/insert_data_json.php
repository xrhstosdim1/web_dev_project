<?php

include('../database/db_conf.php');

if (!isset($_FILES['jsonFile']) || $_FILES['jsonFile']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['code' => 0, 'message' => 'Πρόβλημα κατά το ανέβασμα του αρχείου.']));
}

$fileContent = file_get_contents($_FILES['jsonFile']['tmp_name']);

if ($fileContent === false) {
    die(json_encode(['code' => 0, 'message' => 'Αποτυχία ανάγνωσης του αρχείου.']));
}

$data = json_decode($fileContent, true);

if ($data === null) {
    die(json_encode(['code' => 0, 'message' => 'Αποτυχία αποκωδικοποίησης. Ελέγξτε τη μορφή του αρχείου.']));
}

try {
    if (isset($data['students'])) {
        $user_stmt = $conn->prepare("INSERT IGNORE INTO User (email, name, surname) VALUES (?, ?, ?)");
        $stmt = $conn->prepare("INSERT INTO Students (mob, tel, father_name, email, street, str_number, city, postcode, am) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE mob = VALUES(mob), tel = VALUES(tel), father_name = VALUES(father_name), 
                                street = VALUES(street), str_number = VALUES(str_number), city = VALUES(city), 
                                postcode = VALUES(postcode), am = VALUES(am)");

        foreach ($data['students'] as $student) {
            $name = $student['name'];
            $surname = $student['surname'];
            $student_number = $student['student_number'];
            $street = $student['street'];
            $number = $student['number'];
            $city = $student['city'];
            $postcode = $student['postcode'];
            $father_name = $student['father_name'];
            $landline_telephone = $student['landline_telephone'];
            $mobile_telephone = $student['mobile_telephone'];
            $email = $student['email'];

            $user_stmt->bind_param("sss", $email, $name, $surname);
            $user_stmt->execute();

            $stmt->bind_param("ssssssssi", $mobile_telephone, $landline_telephone, $father_name, $email, $street, $number, $city, $postcode, $student_number);
            $stmt->execute();
        }

        $user_stmt->close();
        $stmt->close();
    }

    if (isset($data['professors'])) {
        $professor_stmt = $conn->prepare("INSERT IGNORE INTO User (email, name, surname) VALUES (?, ?, ?)");
        $professor_insert_stmt = $conn->prepare("INSERT INTO professor (email, topic, dept, uni, land_tel, mob_tel) 
                                                 VALUES (?, ?, ?, ?, ?, ?) 
                                                 ON DUPLICATE KEY UPDATE topic = VALUES(topic), dept = VALUES(dept), uni = VALUES(uni), 
                                                 land_tel = VALUES(land_tel), mob_tel = VALUES(mob_tel)");

        foreach ($data['professors'] as $professor) {
            $name = $professor['name'];
            $surname = $professor['surname'];
            $email = $professor['email'];
            $topic = $professor['topic'];
            $dept = $professor['department'];
            $uni = $professor['university'];
            $land_tel = $professor['landline'];
            $mob_tel = $professor['mobile'];

            $professor_stmt->bind_param("sss", $email, $name, $surname);
            $professor_stmt->execute();

            $professor_insert_stmt->bind_param("ssssss", $email, $topic, $dept, $uni, $land_tel, $mob_tel);
            $professor_insert_stmt->execute();
        }

        $professor_stmt->close();
        $professor_insert_stmt->close();
    }

    echo json_encode([
        'code' => 1,
        'message' => 'Τα δεδομένα από το αρχείο καταχωρήθηκαν επιτυχώς.'
    ]);

} catch (Exception $e) {
    echo json_encode(['code' => 0, 'message' => 'Σφάλμα κατά την εισαγωγή δεδομένων: ' . $e->getMessage()]);
}

$conn->close();




?>
