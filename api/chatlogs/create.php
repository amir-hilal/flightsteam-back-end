<?php

require "../../config/config.php";
include '../utils/cors.php';
require "../utils/auth_middleware.php";
$decoded_token = authenticate_user_or_admin(); // Authenticate user or admin

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input["user_id"];
    $message = $input["message"];
    $sender = $input["sender"];

    $stmt = $conn->prepare('INSERT INTO chatlogs (user_id, message, sender) VALUES (?, ?, ?);');
    $stmt->bind_param('iss', $user_id, $message, $sender);
    try {
        $stmt->execute();
        echo json_encode(["message" => "New chat log created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
