<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require "db.php";

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    echo json_encode(["success" => false, "error" => "Missing patient ID"]);
    exit;
}

try {
    $now = new DateTime();
    $appointments = [];

    $stmt = $conn->prepare("SELECT * FROM appointments WHERE patient_id = ? AND status = 'Scheduled'");
    $stmt->execute([$patientId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as $appt) {
        $apptDateTime = new DateTime($appt['preferred_date'] . ' ' . $appt['preferred_time']);
        $diffHours = ($apptDateTime->getTimestamp() - $now->getTimestamp()) / 3600;

        if (abs($diffHours - 24) < 0.5) {
            $appointments[] = "⏰ Reminder: You have an appointment with the {$appt['dept']} department in 24 hours.";
        } elseif (abs($diffHours - 2) < 0.5) {
            $appointments[] = "⚠️ Reminder: You have an appointment with the {$appt['dept']} department in 2 hours.";
        }
    }

    echo json_encode(["success" => true, "notifications" => $appointments]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
