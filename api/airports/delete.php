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

    // Fetch the airport details before deletion
    $stmt = $conn->prepare('SELECT * FROM airports WHERE airport_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $airport_to_delete = $result->fetch_assoc();

    if ($airport_to_delete) {
        $stmt = $conn->prepare('DELETE FROM airports WHERE airport_id = ?');
        $stmt->bind_param('i', $id);
        try {
            $stmt->execute();
            echo json_encode(["message" => "Airport deleted", "status" => "success", "airport" => $airport_to_delete]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        echo json_encode(["message" => "No airport found with the given ID", "status" => "error"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
