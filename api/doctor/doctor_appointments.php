<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require "../db.php";

try {
    // doctor_id will be passed in query param like ?doctor_id=3
    if (!isset($_GET['doctor_id'])) {
        echo json_encode(["success" => false, "error" => "Doctor ID required"]);
        exit;
    }

    $doctorId = ($_GET['doctor_id']);

    // today’s date
    $today = date("Y-m-d");
    $nextWeek = date("Y-m-d", strtotime("+7 days"));

    // prepare query
    $stmt = $conn->prepare("
        SELECT 
            a.id AS appointment_id,
            p.name AS patient_name,
            p.patient_id AS patient_id,
            a.dept,
            a.preferred_date,
            a.preferred_time,
            a.status,
            a.token_number,
            a.room_number,
            d.name AS doctor_name,
            d.department AS doctor_department
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.doctor_id = ?
        AND a.preferred_date BETWEEN ? AND ?
        AND a.status != 'Done'  
        AND a.status != 'absent' 
        ORDER BY a.preferred_date ASC, a.preferred_time ASC
    ");
    $stmt->execute([$doctorId, $today, $nextWeek]);

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "appointments" => $appointments]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
