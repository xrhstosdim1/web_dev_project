<?php
include('../log-in-system/user_auth.php');
include('../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $thesisId = $input['thesis_id'] ?? null;
    $professorType = $input['professor_type'] ?? null;
    $grades = $input['grades'] ?? null;

    if (!$thesisId || !$professorType || !$grades) {
        throw new Exception('Λείπουν δεδομένα. Παρακαλώ ελέγξτε το αίτημα.');
    }

    $mapping = [
        'epivlepon' => 'prof1',
        'member2' => 'prof2',
        'member3' => 'prof3'
    ];

    if (!isset($mapping[$professorType])) {
        throw new Exception('Μη έγκυρος τύπος καθηγητή.');
    }

    $professorColumn = $mapping[$professorType];

    $fields = [
        'crit1' => "{$professorColumn}_grade_crit_1",
        'crit2' => "{$professorColumn}_grade_crit_2",
        'crit3' => "{$professorColumn}_grade_crit_3",
        'crit4' => "{$professorColumn}_grade_crit_4"
    ];

    $statusCheckQuery = "SELECT status FROM vathmologio WHERE id_diplwmatikis = ?";
    $stmt = $conn->prepare($statusCheckQuery);
    $stmt->bind_param('i', $thesisId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Η διπλωματική δεν βρέθηκε.');
    }

    $row = $result->fetch_assoc();
    if ($row['status'] === 'anamoni_gia_egkrisi') {
        echo json_encode([
            'success' => false,
            'message' => 'Δεν επιτρέπονται αλλαγές στο βαθμολόγιο. Έχει ήδη σταλεί στη γραμματεία.'
        ]);
        exit;
    }

    $updateQuery = "
        UPDATE vathmologio
        SET 
            {$fields['crit1']} = ?,
            {$fields['crit2']} = ?,
            {$fields['crit3']} = ?,
            {$fields['crit4']} = ?
        WHERE id_diplwmatikis = ? AND status = 'prosva8mologisi'
    ";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        'ddddi',
        $grades['crit1'],
        $grades['crit2'],
        $grades['crit3'],
        $grades['crit4'],
        $thesisId
    );

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $response = ['success' => true, 'message' => 'Οι βαθμολογίες αποθηκεύτηκαν επιτυχώς.'];

        $checkQuery = "
            SELECT 
                prof1_grade_crit_1, prof1_grade_crit_2, prof1_grade_crit_3, prof1_grade_crit_4,
                prof2_grade_crit_1, prof2_grade_crit_2, prof2_grade_crit_3, prof2_grade_crit_4,
                prof3_grade_crit_1, prof3_grade_crit_2, prof3_grade_crit_3, prof3_grade_crit_4
            FROM vathmologio
            WHERE id_diplwmatikis = ?
        ";

        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('i', $thesisId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $allGradesFilled = true;

            foreach ($row as $grade) {
                if (is_null($grade)) {
                    $allGradesFilled = false;
                    break;
                }
            }

            if ($allGradesFilled) {
                $statusUpdateQuery = "UPDATE vathmologio SET status = 'anamoni_gia_egkrisi' WHERE id_diplwmatikis = ?";
                $stmt = $conn->prepare($statusUpdateQuery);
                $stmt->bind_param('i', $thesisId);
                $stmt->execute();

                $response['message'] .= ' Το βαθμολόγιο στάλθηκε στη γραμματεία.';
            }
        }

        echo json_encode($response);
    } else {
        throw new Exception('Αποτυχία αποθήκευσης βαθμολογιών.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>