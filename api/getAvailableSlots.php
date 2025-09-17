<?php
header("Content-Type: application/json");
require "db.php";

$doctor_id = $_GET['doctor_id'] ?? '';
$date = $_GET['date'] ?? '';

if (!$doctor_id || !$date) {
    echo json_encode(["error" => "doctor_id and date are required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT preferred_time FROM appointments WHERE doctor_id = ? AND preferred_date = ?");
    $stmt->execute([$doctor_id, $date]);
    $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $slots = [];
    $start = strtotime("09:00");
    $end = strtotime("17:00");
    for ($time = $start; $time < $end; $time += 30*60) {
        $slot = date("H:i", $time);
        if (!in_array($slot, $booked)) {
            $slots[] = $slot;
        }
    }

    echo json_encode($slots);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
