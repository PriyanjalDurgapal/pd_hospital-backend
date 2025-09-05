<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['token'])) {
        echo json_encode(["success" => false, "error" => "Token required"]);
        exit;
    }

    $token = trim($data['token']);

    // Invalidate token
    $stmt = $pdo->prepare("UPDATE patients SET token = NULL WHERE token = :token");
    $stmt->execute([":token" => $token]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Logged out successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid token"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
