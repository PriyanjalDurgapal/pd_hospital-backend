<?php
// Enable error reporting during development
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("./db.php"); // $conn must be a PDO instance

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$required = ["name", "email", "phone", "role", "joiningDate"];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(["error" => "$field is required"]);
        exit;
    }
}

$name        = htmlspecialchars(trim($_POST['name']));
$email       = htmlspecialchars(trim($_POST['email']));
$phone       = htmlspecialchars(trim($_POST['phone']));
$role        = htmlspecialchars(trim($_POST['role']));
$department  = htmlspecialchars(trim($_POST['department'] ?? ''));
$salary      = htmlspecialchars(trim($_POST['salary'] ?? ''));
$joiningDate = htmlspecialchars(trim($_POST['joiningDate']));

function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#%!$';
    return substr(str_shuffle($chars), 0, $length);
}
$plainPassword = generateRandomPassword();
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

// Image upload
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
        echo json_encode(["error" => "Image upload failed"]);
        exit;
    }
}

// Check if email exists
$checkStmt = $conn->prepare("SELECT email FROM staff WHERE email = ?");
$checkStmt->execute([$email]);

if ($checkStmt->rowCount() > 0) {
    echo json_encode(["error" => "Email already exists"]);
    exit;
}

try {
    // Insert into staff table
    $stmt = $conn->prepare("INSERT INTO staff 
        (name, email, password, phone, role, department, salary, joining_date, image, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
        $name,
        $email,
        $hashedPassword,
        $phone,
        $role,
        $department,
        $salary,
        $joiningDate,
        $imagePath
    ]);

    // Get inserted staff ID
    $staff_id = $conn->lastInsertId();

    // If role is doctor, insert into doctors table
    if (strtolower($role) === 'doctor') {
        // Generate a doctor ID (e.g., DR20250911_123)
        $doctor_id = 'DR' . date("Ymd") . "_" . rand(100, 999);

        $docStmt = $conn->prepare("INSERT INTO doctors 
            (doctor_id, staff_id, name, email, phone, department, joining_date, image, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $docStmt->execute([
            $doctor_id,
            $staff_id,
            $name,
            $email,
            $phone,
            $department,
            $joiningDate,
            $imagePath
        ]);
    }

    echo json_encode([
        "success" => true,
        "message" => "Staff added successfully",
        "password" => $plainPassword
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error", "details" => $e->getMessage()]);
}
?>
