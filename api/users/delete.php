<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // Allow the necessary HTTP methods
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null || !isset($data['user_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid input data"]);
        exit();
    }

    $userId = $data['user_id'];

    $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ?');
    $stmt->bind_param('i', $userId);

    try {
        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "User deleted", "status" => "success"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No user found with the given ID", "status" => "error"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        error_log("Database error: " . $e->getMessage());
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
