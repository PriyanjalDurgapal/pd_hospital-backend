<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);
// print_r($data);
// die();

if (!isset($data['patient_id']) || !isset($data['dept']) || !isset($data['date']) || !isset($data['time'])) {
    echo json_encode(["success" => false, "error" => "Missing fields"]);
    exit;
}
$pid=$data['patient_id'];

try {
    $appointmentDateTime = strtotime($data['date'] . ' ' . $data['time']);
    $now = time();

    if ($appointmentDateTime < $now) {
        echo json_encode(["success" => false, "error" => "Appointment must be in the future"]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, dept, preferred_date, preferred_time, status) 
                           VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$pid, $data['dept'], $data['date'], $data['time']]);

    echo json_encode(["success" => true, "message" => "Appointment request created"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
