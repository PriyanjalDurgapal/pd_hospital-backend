<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("../db.php"); 

// Get POST body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["appointment_id"])) {
    echo json_encode(["success" => false, "message" => "Missing appointment_id"]);
    exit;
}

$appointment_id = intval($data["appointment_id"]);
// print_r($appointment_id);
// die();


try {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'absent' WHERE id = ?");
    $stmt->execute([$appointment_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Appointment marked as absent"]);
    } else {
        echo json_encode(["success" => false, "message" => "Appointment not found or already absent"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
