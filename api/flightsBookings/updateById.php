<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";

$decoded_token = authenticate_admin(); // Ensure only admins can access

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['user_id', 'flight_id', 'status', 'booking_date'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $user_id = $data["user_id"];
    $flight_id = $data["flight_id"];
    $status = $data["status"];
    $booking_date = $data["booking_date"];

    $stmt = $conn->prepare('UPDATE bookings SET flight_id = ?, status = ?, booking_date = ? WHERE user_id = ? AND flight_id = ?');
    $stmt->bind_param('issii', $flight_id, $status, $booking_date, $user_id, $flight_id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $stmt = $conn->prepare('SELECT * FROM bookings WHERE user_id = ? AND flight_id = ?');
            $stmt->bind_param('ii', $user_id, $flight_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $updated_booking = $result->fetch_assoc();
            send_response($updated_booking, "Booking updated successfully", 200);
        } else {
            send_response(null, "No booking found with the given user ID or no changes made", 404);
        }
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
