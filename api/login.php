<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

// Get JSON POST data
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit;
}

// First check if user exists
$stmt = $conn->prepare("SELECT * FROM admins WHERE email = :email");
$stmt->execute([":email" => $email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// ✅ Check if account is locked
if ($admin['lock_until'] && strtotime($admin['lock_until']) > time()) {
    echo json_encode([
        "success" => false,
        "message" => "Account locked. Try again after 1 minutes.",
         "locked" => true
    ]);
    exit;
}

//  Validate password
if ($admin['password'] === md5($password)) {
    // Reset failed_attempts if login is successful
    $stmt = $conn->prepare("UPDATE admins SET failed_attempts = 0, lock_until = NULL WHERE id = :id");
    $stmt->execute([":id" => $admin['id']]);
    

   
    $_SESSION['user_id'] = $admin['id'];
    // session_regenerate_id(true);
    // print_r($_SESSION['user_id']);
    // Generate token
    $token = base64_encode($admin['id'] . ":" . time());
    echo json_encode(["success" => true, "token" => $token]);

} else {
    // Wrong password → increase failed_attempts
    $failedAttempts = $admin['failed_attempts'] + 1;

    if ($failedAttempts >= 3) {
        // Lock account for 15 minutes
        $lockUntil = date("Y-m-d H:i:s", strtotime("+1 minutes"));
        $stmt = $conn->prepare("UPDATE admins SET failed_attempts = :fa, lock_until = :lu WHERE id = :id");
        $stmt->execute([":fa" => $failedAttempts, ":lu" => $lockUntil, ":id" => $admin['id']]);

        echo json_encode(["success" => false, "message" => "Account locked due to multiple failed attempts. Try again in 1 minutes." , "locked" => true]);
    } else {
        // Just update failed_attempts
        $stmt = $conn->prepare("UPDATE admins SET failed_attempts = :fa WHERE id = :id");
        $stmt->execute([":fa" => $failedAttempts, ":id" => $admin['id']]);

        echo json_encode(["success" => false, "message" => "Invalid password. Attempt $failedAttempts of 3"]);
    }
}
?>
