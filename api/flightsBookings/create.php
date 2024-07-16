<?php
require "../../config/config.php";
require "../utils/response.php";
include '../utils/cors.php';
require "../utils/jwt.php"; // Include the JWT validation functions

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        send_response(null, "Authorization header not found", 401);
        exit();
    }

    $authHeader = $headers['Authorization'];
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        send_response(null, "Bearer token not found", 401);
        exit();
    }

    $decoded = validate_jwt_token($jwt);

    if (!$decoded) {
        send_response(null, "Invalid token", 401);
        exit();
    }

    $user_id = $decoded['user_id'];

    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['seats', 'flight_id'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $seats = $data['seats'];
    $flight_id = $data['flight_id'];

    $stmt = $conn->prepare('INSERT INTO bookings (user_id, flight_id) VALUES (?, ?)');

    if ($stmt === false) {
        send_response(null, "Prepare failed: (" . $conn->errno . ") " . $conn->error, 500);
        exit();
    }

    try {
        $conn->begin_transaction();
        for ($i = 0; $i < $seats; $i++) {
            $stmt->bind_param('ii', $user_id, $flight_id);
            $stmt->execute();
        }
        $conn->commit();
        send_response(null, "Booking successful", 201);
    } catch (Exception $e) {
        $conn->rollback();
        send_response(null, "Booking failed: " . $e->getMessage(), 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
