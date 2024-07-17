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

    $taxi_id = $data["taxi_id"];

    $stmt = $conn->prepare('DELETE FROM Taxis WHERE taxi_id = ?;');
    $stmt->bind_param('i', $taxi_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "Taxi deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
