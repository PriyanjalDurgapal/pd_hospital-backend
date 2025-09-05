<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require "db.php";
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  

  $stmt = $pdo->prepare("SELECT * FROM login_media ORDER BY created_at DESC");
  $stmt->execute();
  $media = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($media );

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
?>