<?php
include('../database/db_conf.php');
include('../log-in-system/user_auth.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $topic = trim($_POST['topic']);
    $summary = trim($_POST['summary']);
    $creation_date = date('Y-m-d H:i:s');
    $status = 'pros_anathesi';
    $student_am = trim($_POST['student-am'] ?? '');

    if (empty($topic) || empty($summary)) {
        echo json_encode(['success' => false, 'message' => 'Συμπληρώστε όλα τα υποχρεωτικά πεδία.']);
        exit();
    }

    // file uipload manage
    $fileName = null;
    if (isset($_FILES['upload-file']) && $_FILES['upload-file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['upload-file'];
        $allowedTypes = ['application/pdf'];

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Το αρχείο πρέπει να είναι σε μορφή PDF.']);
            exit();
        }

        $uploadDir = '../uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!is_writable($uploadDir)) {
            echo json_encode(['success' => false, 'message' => 'Λείπουν write permissions.']);
            exit();
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά το ανέβασμα του αρχείου.']);
            exit();
        }
    }

    // insert diplwmatiki_ka8igita
    $stmt = $conn->prepare("INSERT INTO diplwmatiki_ka8igita (email, creation_date, topic, summary, file_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $creation_date, $topic, $summary, $fileName);

    if ($stmt->execute()) {
        $diplwmatikiId = $stmt->insert_id;

        if (!empty($student_am)) {
            // insert diplwmatiki_foitita
            $stmtStudent = $conn->prepare("INSERT INTO diplwmatiki_foitita (am_foititi, id_diplwmatikis) VALUES (?, ?)");
            $stmtStudent->bind_param("ii", $student_am, $diplwmatikiId);
            
            if (!$stmtStudent->execute()) {
                echo json_encode(['success' => false, 'message' => 'Η διπλωματική ανακοινώθηκε, αλλά υπήρξε σφάλμα με την ανάθεση στον φοιτητή.']);
                exit();
            }
        
            // Update diplwmatiki_ka8igita
            $stmtUpdate = $conn->prepare("UPDATE diplwmatiki_ka8igita SET status = 'pros_anathesi' WHERE id = ?");
            $stmtUpdate->bind_param("i", $diplwmatikiId);
            
            if (!$stmtUpdate->execute()) {
                echo json_encode(['success' => false, 'message' => 'Η διπλωματική ανακοινώθηκε, αλλά υπήρξε σφάλμα με την ενημέρωση του καθηγητή.']);
                exit();
            }
        
            echo json_encode(['success' => true, 'message' => 'Η διπλωματική ανακοινώθηκε και ανατέθηκε επιτυχώς.']);
            exit();
        }

        echo json_encode(['success' => true, 'message' => 'Η διπλωματική ανακοινώθηκε επιτυχώς.']);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ανακοίνωση της διπλωματικής.']);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Μη έγκυρη μέθοδος πρόσβασης.']);
exit();
?>