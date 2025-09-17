<?php
// Enable error reporting during development (optional)
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("./db.php"); // Ensure this defines $conn (PDO instance)

// Get the POST data (email and password from the login form)
$data = json_decode(file_get_contents("php://input"), true);

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// Validate the required fields
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(["error" => "Email and password are required"]);
    exit;
}

// Get the entered email and password (trim spaces)
$email = trim($data['email']);
$password = trim($data['password']); // Trim any leading or trailing spaces

try {
    // Prepare the SQL query to fetch the user by email and role ('Doctor')
    $stmt = $conn->prepare("SELECT * FROM staff WHERE email = ? AND role = 'Doctor' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: print user info
    // print_r($user);
    // die();

    // Check if the user exists and verify the entered password with the hashed one stored in the database
    if (!$user) {
        echo json_encode(["error" => "User not found"]);
        exit;
    }

    // Compare the entered password with the stored hash
    if (!password_verify($password, $user['password'])) {
        // If the password doesn't match
        echo json_encode(["error" => "Invalid credentials"]);
        exit;
    }

    $staff_id = $user["id"];

    // Create a secure session token (for example, JWT or random bytes)
    $token = bin2hex(random_bytes(32)); // Random 32-byte token
    $update = $conn->prepare("UPDATE staff SET token = ? WHERE id = ?");
    $update->execute([$token, $staff_id]);

    // Fetch doctor_id from doctors table
    $stm = $conn->prepare("SELECT doctor_id FROM doctors WHERE staff_id = ?");
    $stm->execute([$staff_id]);
    $doctor = $stm->fetch(PDO::FETCH_ASSOC);

    // Debugging: print doctor info
    // print_r($doctor);
    // die();

    // Return success response with user details and token
    echo json_encode([
        "success"   => true,
        "message"   => "Login successful",
        "token"     => $token,
        "name"      => $user['name'],
        "doctor_id" => $doctor['doctor_id'] ?? null,
        "email"     => $user['email'],
        "role"      => $user['role'],
    ]);

} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(["error" => "Database error", "details" => $e->getMessage()]);
}
?>
