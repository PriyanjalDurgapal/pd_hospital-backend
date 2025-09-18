<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once("../db.php");

// patient_id from query string
$patient_id = $_GET['patient_id'] ?? null;
// print_r($patient_id);
// die();

if (!$patient_id) {
    echo json_encode(["success" => false, "message" => "Patient ID required"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            p.id AS prescription_id,
            p.appointment_id,
            p.doctor_id,
            d.name,
            p.patient_id,
            p.patient_name,
            p.symptoms,
            p.prescription,
            p.next_appointment,
            p.no_next_appointment,
            p.created_at
        FROM prescriptions p
        LEFT JOIN doctors d ON p.doctor_id = d.doctor_id
        WHERE p.patient_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$patient_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "records" => $records]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
