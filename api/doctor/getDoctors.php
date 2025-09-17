<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "../db.php";

try {
    if (!isset($_GET['doctor_id'])) {
        echo json_encode(["success" => false, "message" => "doctor_id required"]);
        exit;
    }

    $doctor_id = $_GET['doctor_id'];

    $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = :doctor_id LIMIT 1");
    $stmt->execute([":doctor_id" => $doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doctor) {
        echo json_encode($doctor);
    } else {
        echo json_encode(["success" => false, "message" => "Doctor not found"]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
