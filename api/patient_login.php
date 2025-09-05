<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require "db.php";  // 🔹 contains $host, $dbname, $user, $pass

try {
    // Create DB connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get input JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['email']) || !isset($data['password'])) {
        echo json_encode(["success" => false, "error" => "Email and password are required"]);
        exit;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // Check if patient exists
    $stmt = $pdo->prepare("SELECT id, patient_id, name, email, password FROM patients WHERE email = :email");
    $stmt->execute([":email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            
            // ✅ Generate a secure token
            $token = bin2hex(random_bytes(32));

            // ✅ Save token in DB
            $update = $pdo->prepare("UPDATE patients SET token = :token WHERE id = :id");
            $update->execute([
                ":token" => $token,
                ":id" => $user['id']
            ]);

            echo json_encode([
                "success" => true,
                "patient_id" => $user['patient_id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "token" => $token   // ✅ return token to frontend
            ]);
        } else {
            echo json_encode(["success" => false, "error" => "Invalid password"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "No account found with this email"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
