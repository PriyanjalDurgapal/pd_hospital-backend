<?php
// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//     header("Access-Control-Allow-Origin: *");
//     header("Access-Control-Allow-Methods: POST, OPTIONS");
//     header("Access-Control-Allow-Headers: Content-Type");
//     http_response_code(200);
//     exit();
// }

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require  "../db.php";

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['staff_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid request, staff_id missing"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE doctors SET 
                                name = :name,
                                email = :email,
                                phone = :phone,
                                department = :department,
                                joining_date = :joining_date
                            WHERE staff_id = :staff_id");

    $updated = $stmt->execute([
        ":name"         => $data['name'] ?? null,
        ":email"        => $data['email'] ?? null,
        ":phone"        => $data['phone'] ?? null,
        ":department"   => $data['department'] ?? null,
        ":joining_date" => $data['joining_date'] ?? null,
        ":staff_id"     => $data['staff_id']
    ]);

    if ($updated) {
        echo json_encode(["success" => true, "message" => "Doctor updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed"]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
