<?php
session_start();
error_reporting(E_ALL);
// ini_set('display_errors', 0);
// CORS headers for React localhost
header("Access-Control-Allow-Origin: *");// your React app
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require "db.php";

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// print_r($_SESSION['user_id']);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$id = $_SESSION['user_id'];

// Fetch user from database
$stmt = $conn->prepare("SELECT id, name, email FROM admins WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    http_response_code(200);
    echo json_encode($row); // {id, name, email}
} else {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
