<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";


try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT * FROM staff ORDER BY id DESC");
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If image field is only filename, prepend full URL
    foreach ($staff as &$s) {
        $s['image'] = "http://localhost/pd_hospital_backend/uploads/" . $s['image'];
    }

    echo json_encode(["success" => true, "data" => $staff]);

} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
