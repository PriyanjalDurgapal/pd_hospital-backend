<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("../db.php"); 

$data = json_decode(file_get_contents("php://input"), true);
// $data = [
//     "appointment_id" => 123,
//     "doctor_id" => "DR20250917_576",
//     "patient_id" => "PAT544020",
//     "patient_name" => "Milan",
//     "symptoms" => "Chest pain, shortness of breath",
//     "prescription" => "Aspirin 100mg, Ibuprofen 200mg",
//     "next_appointment" => "2025-09-20",
//     "no_next_appointment" => 0 // Change to 1 if no next appointment
// ];
// print_r($data);
// die();
$appointment_id = $data["appointment_id"] ?? null;
$doctor_id = $data["doctor_id"] ?? null;
$patient_id = $data["patient_id"] ?? null;
$patient_name = $data["patient_name"] ?? null;
$symptoms = $data["symptoms"] ?? "";
$prescription = $data["prescription"] ?? "";
$next_appointment = $data["next_appointment"] ?? "0";
$no_next_appointment = $data["no_next_appointment"] ?? 0;
// print_r([
//     'appointment_id' => $appointment_id,
//     'doctor_id' => $doctor_id,
//     'patient_id' => $patient_id,
//     'patient_name' => $patient_name,
//     'symptoms' => $symptoms,
//     'prescription' => $prescription,
//     'next_appointment' => $next_appointment,
//     'no_next_appointment' => $no_next_appointment
// ]);

// die();

if (!$appointment_id || !$doctor_id || !$patient_id || !$patient_name) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}


try {
    
    // ✅ Insert into prescriptions table
    $stmt = $conn->prepare("
        INSERT INTO prescriptions 
        (appointment_id, doctor_id, patient_id, patient_name, symptoms, prescription, next_appointment, no_next_appointment) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $appointment_id,
        $doctor_id,
        $patient_id,
        $patient_name,
        $symptoms,
        $prescription,
        $next_appointment,
        $no_next_appointment
    ]);

    // ✅ Update appointment status
    $stmt2 = $conn->prepare("UPDATE appointments SET status = 'done' WHERE id = ?");
    $stmt2->execute([$appointment_id]);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
