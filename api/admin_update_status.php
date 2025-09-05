<?php
// CORS preflight handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}

// Main CORS headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

try {
    // Read raw JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id']) || !isset($data['is_active'])) {
        throw new Exception("Missing required fields: id or is_active");
    }

    $id = intval($data['id']);
    $isActive = intval($data['is_active']);

    // Update media status
    $stmt = $conn->prepare("UPDATE login_media SET is_active = :active WHERE id = :id");
    $stmt->execute([
        ':active' => $isActive,
        ':id' => $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Media status updated",
        "updated_status" => $isActive
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
