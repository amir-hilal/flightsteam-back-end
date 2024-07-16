<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
include '../utils/cors.php';

$admin = authenticate_admin(); // Ensure only admins can delete

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    if (empty($data['booking_id'])) {
        send_response(null, 'booking_id cannot be null or empty', 400);
        exit();
    }

    $booking_id = $data['booking_id'];

    $stmt = $conn->prepare('SELECT * FROM bookings WHERE booking_id = ?');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking_to_delete = $result->fetch_assoc();

    if ($booking_to_delete) {
        $stmt = $conn->prepare('DELETE FROM bookings WHERE booking_id = ?');
        $stmt->bind_param('i', $booking_id);
        try {
            $stmt->execute();
            send_response($booking_to_delete, 'Booking deleted successfully', 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        send_response(null, 'No booking found with the given ID', 404);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
