<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require "db.php";           // your DB connection
require "verifyToken.php";  // reusable token verification function

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $token = trim($data['token'] ?? '');

    // Verify token
    $result = verifyToken($conn, $token);

    echo json_encode($result);
    exit;

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}
