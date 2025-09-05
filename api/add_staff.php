<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("./db.php"); // this should define $conn

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// Debug log POST + FILES
file_put_contents(__DIR__ . "/debug.log", "POST:\n" . print_r($_POST, true) . "\nFILES:\n" . print_r($_FILES, true) . "\n\n", FILE_APPEND);

// Validate required fields
$required = ["name", "email", "phone", "role", "joiningDate"];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(["error" => "$field is required"]);
        exit;
    }
}

// Sanitize input
$name        = htmlspecialchars(trim($_POST['name']));
$email       = htmlspecialchars(trim($_POST['email']));
$phone       = htmlspecialchars(trim($_POST['phone']));
$role        = htmlspecialchars(trim($_POST['role']));
$department  = htmlspecialchars(trim($_POST['department'] ?? ''));
$salary      = htmlspecialchars(trim($_POST['salary'] ?? ''));
$joiningDate = htmlspecialchars(trim($_POST['joiningDate']));

// Handle Image Upload
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $uploadDir = __DIR__ . "/../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = "uploads/" . $fileName;
    } else {
        echo json_encode(["error" => "Image upload failed", "debug" => $_FILES['image']]);
        exit;
    }
}

// Check DB connection
if (!isset($conn)) {
    echo json_encode(["error" => "Database connection (\$conn) not found. Check db.php"]);
    exit;
}

// Insert into DB
try {
    $stmt = $conn->prepare("INSERT INTO staff 
        (name, email, phone, role, department, salary, joining_date, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $role, $department, $salary, $joiningDate, $imagePath]);

    echo json_encode(["message" => " Staff added successfully"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error", "details" => $e->getMessage()]);
}
