<?php
include('../log-in-system/user_auth.php');
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idDiplomatikis = $_POST['id_diplomatikis'] ?? null;
    $profEmail = $_SESSION['email'] ?? null;
    $comment = trim($_POST['comment'] ?? '');

    if (!$idDiplomatikis) {
        echo json_encode([
            'success' => false,
            'message' => 'Λείπει id διπλωματικής.'
        ]);
        exit();
    }

    if (!$profEmail) {
        echo json_encode([
            'success' => false,
            'message' => 'Λείπει email καθηγητή.'
        ]);
        exit();
    }


    if (empty($comment)) {
        echo json_encode([
            'success' => false,
            'message' => 'Λείπει το σχόλιο.'
        ]);
        exit();
    }

    try {
        $sql = "INSERT INTO professor_comments_on_theses (id_diplomatikis, prof_email, comment)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Πρόβλημα στο prepare statement: " . $conn->error);
        }

        $stmt->bind_param("iss", $idDiplomatikis, $profEmail, $comment);

        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα εκτέλεσης του statement: " . $stmt->error);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Το σχόλιο καταχωρήθηκε επιτυχώς!'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Μη έγκυρη μέθοδος πρόσβασης.']);
?>