<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "../db.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);
    $token = $_GET["token"] ?? null;

    if (!$token) {
        echo json_encode(["success" => false, "error" => "Token number required"]);
        exit;
    }

    // Search today's appointment by token
    $today = date("Y-m-d");
    $stmt = $pdo->prepare("
        SELECT p.patient_id, p.name, p.email, p.phone, a.token_number
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.token_number = :token AND a.preferred_date = :today
        LIMIT 1
    ");
    $stmt->execute([":token" => $token, ":today" => $today]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        echo json_encode(["success" => true, "patient" => $patient]);
    } else {
        echo json_encode(["success" => false, "error" => "No patient found"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
