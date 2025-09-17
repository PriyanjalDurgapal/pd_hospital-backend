<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET"); // usually GET for fetching
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

try {
    $stmt = $conn->query("
        SELECT 
            a.id AS appointment_id,
            p.name AS patient_name,
            a.dept,
            a.preferred_date,
            a.preferred_time,
            a.status,
            a.token_number,
            a.room_number,
            d.doctor_id AS doctor_id,
            d.name AS doctor_name,
            d.department AS doctor_department,
            d.image AS doctor_image
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
        ORDER BY a.created_at DESC
    ");

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($appointments ?: []);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
