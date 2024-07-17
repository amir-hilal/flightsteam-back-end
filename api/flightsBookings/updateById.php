<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
require "../utils/validator.php";
include '../utils/cors.php';

$decoded_token = authenticate_admin(); // Ensure only admins can access

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    // Validate input data
    $required_fields = ['user_id', 'flight_id', 'status', 'booking_date'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $user_id = $data["user_id"];
    $flight_id = $data["flight_id"];
    $status = $data["status"];
    $booking_date = $data["booking_date"];

    if (!validate_int($user_id)) {
        send_response(null, 'Invalid user ID', 400);
        exit();
    }

    if (!validate_int($flight_id)) {
        send_response(null, 'Invalid flight ID', 400);
        exit();
    }

    if (!validate_booking_status($status)) {
        send_response(null, 'Invalid status', 400);
        exit();
    }

    if (!validate_date($booking_date) && !validate_datetime($booking_date)) {
        send_response(null, 'Invalid booking date', 400);
        exit();
    }

    // Check if the flight_id exists
    $stmt = $conn->prepare('SELECT * FROM flights WHERE flight_id = ?');
    $stmt->bind_param('i', $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        send_response(null, 'Invalid flight ID', 400);
        exit();
    }

    // Check if the user_id exists
    $stmt = $conn->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        send_response(null, 'Invalid user ID', 400);
        exit();
    }

    // Proceed to update the booking
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
