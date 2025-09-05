<?php

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}


header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

try {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data["id"])) {
        throw new Exception("Media ID is required");
    }

    $id = intval($data["id"]);

    $stmt = $conn->prepare("SELECT media_url FROM login_media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$media) {
        throw new Exception("Media not found");
    }

    $stmt = $conn->prepare("DELETE FROM login_media WHERE id = ?");
    $stmt->execute([$id]);

    $filePath = __DIR__ . "/" . $media["media_url"];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    echo json_encode(["success" => true, "message" => "Media deleted"]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
