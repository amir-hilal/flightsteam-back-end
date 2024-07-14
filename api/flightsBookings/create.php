<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
require "../utils/validator.php";

$auth_data = authenticate_user_or_admin(); // Ensure only users and admins can create a booking

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    // Validate input data
    $required_fields = ['flight_id', 'status', 'booking_date'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $user_id = $auth_data['user_id']; // Get user_id from the decoded token
    $flight_id = $data['flight_id'];
    $status = $data['status'];
    $booking_date = $data['booking_date'];

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

    // Proceed to create the booking
    $stmt = $conn->prepare('INSERT INTO bookings (user_id, flight_id, status, booking_date) VALUES (?, ?, ?, ?);');
    $stmt->bind_param('iiss', $user_id, $flight_id, $status, $booking_date);
    try {
        $stmt->execute();
        $booking_id = $stmt->insert_id;
        $stmt = $conn->prepare('SELECT * FROM bookings WHERE booking_id = ?');
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $created_booking = $result->fetch_assoc();
        send_response($created_booking, 'New booking created', 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
