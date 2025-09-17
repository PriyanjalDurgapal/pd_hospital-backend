<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include "db.php"; // your PDO connection

// Handle preflight request (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents("php://input"), true);

$staff_id = $data['staff_id'] ?? null;
$currentPassword = $data['currentPassword'] ?? null;
$newPassword = $data['newPassword'] ?? null;

if (!$staff_id || !$currentPassword || !$newPassword) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

// Fetch staff user
$query = $conn->prepare("SELECT password FROM staff WHERE id = :id");
$query->execute([":id" => $staff_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// Verify old password
if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode(["success" => false, "message" => "Current password is incorrect"]);
    exit;
}

// Update with new password (hashed)
$newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

$update = $conn->prepare("UPDATE staff SET password = :password WHERE id = :id");
$success = $update->execute([
    ":password" => $newHashedPassword,
    ":id" => $staff_id
]);

if ($success) {
    echo json_encode(["success" => true, "message" => "Password updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Error updating password"]);
}
?>
