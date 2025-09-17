<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "../db.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $patient_id = $_GET["patient_id"] ?? null;

    if (!$patient_id) {
        echo json_encode(["success" => false, "error" => "Patient ID required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT mh.history_id, mh.symptoms, mh.diagnosis, mh.prescription, mh.created_at,
               d.doctor_id, d.name as doctor_name
        FROM medical_history mh
        JOIN doctors d ON mh.doctor_id = d.doctor_id
        WHERE mh.patient_id = :patient_id
        ORDER BY mh.created_at DESC
    ");
    $stmt->execute([":patient_id" => $patient_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "history" => $history]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
