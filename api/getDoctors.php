<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once("./db.php"); // PDO connection

try {
    $stmt = $conn->prepare("SELECT doctor_id AS doctor_id, name, department, status AS availability FROM doctors ORDER BY name ASC");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // print_r($doctor);
    // die();

    echo json_encode($doctors ?: []); // always array
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error", "details" => $e->getMessage()]);
}