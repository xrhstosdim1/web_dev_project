<?php
include('../../database/db_conf.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $idDiplwmatikis = isset($_GET['id']) ? intval($_GET['id']) : null;
    $amFoititi = isset($_GET['am_foititi']) ? intval($_GET['am_foititi']) : null;

    $sql = "
        SELECT 
            v.id_diplwmatikis,
            v.prof1,
            CONCAT(u1.name, ' ', u1.surname) AS prof1_name,
            v.prof1_grade_crit_1,
            v.prof1_grade_crit_2,
            v.prof1_grade_crit_3,
            v.prof1_grade_crit_4,
            v.prof1_final_grade,
            v.prof2,
            CONCAT(u2.name, ' ', u2.surname) AS prof2_name,
            v.prof2_grade_crit_1,
            v.prof2_grade_crit_2,
            v.prof2_grade_crit_3,
            v.prof2_grade_crit_4,
            v.prof2_final_grade,
            v.prof3,
            CONCAT(u3.name, ' ', u3.surname) AS prof3_name,
            v.prof3_grade_crit_1,
            v.prof3_grade_crit_2,
            v.prof3_grade_crit_3,
            v.prof3_grade_crit_4,
            v.prof3_final_grade,
            DATE_FORMAT(g.date_requested, '%d/%m/%Y') AS date_requested
        FROM 
            vathmologio v
        LEFT JOIN 
            professor p1 ON v.prof1 = p1.email
        LEFT JOIN 
            User u1 ON p1.email = u1.email
        LEFT JOIN 
            professor p2 ON v.prof2 = p2.email
        LEFT JOIN 
            User u2 ON p2.email = u2.email
        LEFT JOIN 
            professor p3 ON v.prof3 = p3.email
        LEFT JOIN 
            User u3 ON p3.email = u3.email
        LEFT JOIN 
            gramateia g ON v.id_diplwmatikis = g.id_diplwmatikis AND g.aithsh_gia = 'pros_egrisi_oloklirwmenh'
        LEFT JOIN 
            diplwmatiki_foitita df ON v.id_diplwmatikis = df.id_diplwmatikis
        WHERE 1 ";

    if ($idDiplwmatikis) {
        $sql .= " AND v.id_diplwmatikis = ?";
    }

    if ($amFoititi) {
        $sql .= " AND df.am_foititi = ?";
    }

    $stmt = $conn->prepare($sql);

    if ($idDiplwmatikis && $amFoititi) {
        $stmt->bind_param("ii", $idDiplwmatikis, $amFoititi);
    } elseif ($idDiplwmatikis) {
        $stmt->bind_param("i", $idDiplwmatikis);
    } elseif ($amFoititi) {
        $stmt->bind_param("i", $amFoititi);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκαν δεδομένα για τα κριτήρια που δώσατε.']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
