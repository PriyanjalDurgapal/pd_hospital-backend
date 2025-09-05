<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

try {
    // Check file
    if (!isset($_FILES['media_file'])) {
        throw new Exception("No file uploaded");
    }

    $file = $_FILES['media_file'];
    $mediaType = $_POST['media_type'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    // Upload dir
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Unique name
    $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($file['name']));
    $targetFile = $uploadDir . $filename;

    // Detect mime type
    $fileType = mime_content_type($file['tmp_name']);
    error_log("Uploaded file type: " . $fileType);

    // Allowed types
    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $allowedVideoTypes = [
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',     // .mov
        'video/x-matroska',    // .mkv
        'video/avi',           // .avi
        'video/x-msvideo'
    ];

    if ($mediaType === 'image' && !in_array($fileType, $allowedImageTypes)) {
        throw new Exception("Invalid image file type: " . $fileType);
    }
    if ($mediaType === 'video' && !in_array($fileType, $allowedVideoTypes)) {
        throw new Exception("Invalid video file type: " . $fileType);
    }

    // Move file
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        throw new Exception("Failed to move uploaded file");
    }

    $mediaUrl = "uploads/" . $filename;

    // Insert DB
    $stmt = $conn->prepare("INSERT INTO login_media (media_type, media_url, title, description, is_active) 
                            VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$mediaType, $mediaUrl, $title, $description]);

    echo json_encode(["success" => true, "url" => $mediaUrl]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
