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

$doctor_id = $data['doctor_id'] ?? null;
$currentPassword = $data['currentPassword'] ?? null;
$newPassword = $data['newPassword'] ?? null;

if (!$doctor_id || !$currentPassword || !$newPassword) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}
$quer = $conn->prepare("SELECT staff_id FROM doctors WHERE doctor_id = :did");
$quer->execute([":did" => $doctor_id]);
$staff_id = $quer->fetch(PDO::FETCH_ASSOC);
// Check if we got the staff_id
if (!$staff_id) {
    echo json_encode(["success" => false, "message" => "Doctor not found"]);
    exit;
}

// extract the staff_id 
$staff_id = $staff_id['staff_id'];


// Fetch staff user
$query = $conn->prepare("SELECT password FROM staff WHERE id = :id");
$query->execute([":id" => $staff_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);
// print_r($user);
// die();

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
