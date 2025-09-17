<?php
$host = "localhost";
$user = "root";
$pass = "priyanjal";       // your MySQL password
$dbname = "pd_hospital";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}
?>
