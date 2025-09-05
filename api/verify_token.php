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
    // print_r($token);
    // die();

    // Check token validity
    $stmt = $pdo->prepare("SELECT patient_id, name, email FROM patients WHERE token = :token LIMIT 1");
    $stmt->execute([":token" => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        

        echo json_encode([
            "success" => true,
            "patient" => $user
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid or expired token"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
