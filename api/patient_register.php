<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php"; // contains $host, $dbname, $user, $pass

try {
   
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    $data = $_POST;

    if (!$data) {
        $data = json_decode(file_get_contents("php://input"), true);
    }

    if (!$data || !isset($data['name'], $data['email'], $data['password'])) {
        throw new Exception("Missing required fields");
    }

    // Forn unique patient_id 
    $patientId = "PAT" . str_pad(rand(0, 999999), 6, "0", STR_PAD_LEFT);

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    
    $stmt = $pdo->prepare("INSERT INTO patients 
        (patient_id, name, email, phone, age, gender, password, emergency_name, emergency_phone) 
        VALUES (:patient_id, :name, :email, :phone, :age, :gender, :password, :emergency_name, :emergency_phone)");

    $stmt->execute([
        ":patient_id" => $patientId,
        ":name" => $data['name'],
        ":email" => $data['email'],
        ":phone" => $data['phone'] ?? null,
        ":age" => $data['age'] ?? null,
        ":gender" => $data['gender'] ?? null,
        ":password" => $hashedPassword,
        ":emergency_name" => $data['emergencyName'] ?? null,
        ":emergency_phone" => $data['emergencyPhone'] ?? null
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Patient registered successfully",
        "patient_id" => $patientId
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
