<?php
// verifyToken.php
function verifyToken($conn, $token) {
    if (empty($token)) {
        return ["success" => false, "error" => "Token is required"];
    }

    $stmt = $conn->prepare("SELECT *  FROM patients WHERE token = :token LIMIT 1");
    $stmt->execute([":token" => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        return ["success" => true, "patient" => $user];
    } else {
        return ["success" => false, "error" => "Invalid or expired token"];
    }
}
