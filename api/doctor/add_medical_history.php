<?php


header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "../db.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data["patient_id"] || !$data["doctor_id"]) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO medical_history (patient_id, doctor_id, token_number, symptoms, diagnosis, prescription, created_at)
        VALUES (:patient_id, :doctor_id, :token_number, :symptoms, :diagnosis, :prescription, NOW())
    ");
    $stmt->execute([
        ":patient_id"   => $data["patient_id"],
        ":doctor_id"   => $data["doctor_id"],
        ":token_number"=> $data["token_number"] ?? null,
        ":symptoms"    => $data["symptoms"] ?? "",
        ":diagnosis"   => $data["diagnosis"] ?? "",
        ":prescription"=> $data["prescription"] ?? ""
    ]);

    echo json_encode(["success" => true, "message" => "Medical history saved"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
