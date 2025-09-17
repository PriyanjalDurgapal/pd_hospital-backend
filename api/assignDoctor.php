<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);


$appointment_id = $data['appointment_id'] ?? '';
$doctor_id = $data['doctor_id'] ?? ''; // Expecting numerical id
$preferred_date = $data['date'] ?? '';
$preferred_time = $data['time'] ?? '';

// print_r( "doctor_id " . $doctor_id );
// die();


if (!$appointment_id || !$doctor_id || !$preferred_date || !$preferred_time) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

// Start transaction
$conn->beginTransaction();

try {
    // Get appointment details
    $apptStmt = $conn->prepare("SELECT dept, patient_id FROM appointments WHERE id = ?");
    $apptStmt->execute([$appointment_id]);
    $appointment = $apptStmt->fetch(PDO::FETCH_ASSOC);
    if (!$appointment) throw new Exception("Appointment not found");

    $dept = $appointment['dept'];
    $patient_id = $appointment['patient_id'];

    // print_r("dept- ".$dept."   " ."pid"." " .$patient_id);
    // die();

    // Validate selected doctor availability
    $docStmt = $conn->prepare("
        SELECT doctor_id 
        FROM doctors 
        WHERE doctor_id = ? AND department = ? AND status = 1
        AND id NOT IN (
            SELECT doctor_id 
            FROM appointments 
            WHERE doctor_id = ? AND preferred_date = ? AND preferred_time = ? AND status = 'Scheduled'
        )
    ");
   
    
    // error_log("Doctor query: id=$doctor_id, dept='$dept', date='$preferred_date', time='$preferred_time'");
    $docStmt->execute([$doctor_id, $dept, $doctor_id, $preferred_date, $preferred_time]);
    $doctor = $docStmt->fetch(PDO::FETCH_ASSOC);

    // error_log("Doctor query result: " . json_encode($doctor));
    
    if (!$doctor) {
        throw new Exception("Doctor not available at the selected date/time");
    }

    // Generate FIFO token per doctor per date
    $tokenStmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE preferred_date = ? AND doctor_id = ? AND status = 'Scheduled'
    ");
    $tokenStmt->execute([$preferred_date, $doctor_id]);
    $row = $tokenStmt->fetch(PDO::FETCH_ASSOC);
    $token_number = $row['count'] + 1;

    // Auto-assign room number based on department
    $room_map = [
        'Cardiology' => 'Room 101',
        'Orthopedics' => 'Room 201',
        'Dermatology' => 'Room 301',
        'Neurology' => 'Room 401'
    ];
    $room_number = $room_map[$dept] ?? 'Room Unknown';

    // print_r($doctor_id);
    // die();
    // Update appointment
    $updateStmt = $conn->prepare("
        UPDATE appointments 
        SET doctor_id = ?, status = 'Scheduled', preferred_date = ?, preferred_time = ?, token_number = ?, room_number = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$doctor_id, $preferred_date, $preferred_time, $token_number, $room_number, $appointment_id]);

    // Fetch patient email
    $patientStmt = $conn->prepare("SELECT email FROM patients WHERE patient_id = ?");
    $patientStmt->execute([$patient_id]);
    $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);

    $conn->commit();

    echo json_encode([
        "success" => true,
        "status" => 'Scheduled',
        "doctor_id" => $doctor_id,
        "token_number" => $token_number,
        "room_number" => $room_number,
        "patient_email" => $patient['email'] ?? null
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Assign doctor error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to assign doctor: " . $e->getMessage()]);
}