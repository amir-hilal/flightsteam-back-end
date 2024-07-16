<?php
require "../../config/config.php";
include '../utils/cors.php';
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can delete

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $id = $data['id'];

    $stmt = $conn->prepare('DELETE FROM users WHERE user_id=?');
    $stmt->bind_param('i', $id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "User deleted", "status" => "success"]);
        } else {
            echo json_encode(["message" => "No user found with the given ID", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
