<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);
// print_r($data['appointment_id']);
// die();

if (!isset($data['appointment_id'])) {
    echo json_encode(["success" => false, "error" => "Missing appointment_id"]);
    exit;
}

$stmt = $conn->prepare("UPDATE appointments SET status = 'Rejected' WHERE id = ?");
$stmt->execute([$data['appointment_id']]);

echo json_encode(["success" => true, "message" => "Appointment rejected"]);
