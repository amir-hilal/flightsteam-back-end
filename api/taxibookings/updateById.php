<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/validator.php";
require "../utils/response.php";
include '../utils/cors.php';

$decoded_token = authenticate_admin(); // Authenticate admin

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['taxi_booking_id', 'user_id', 'taxi_id', 'pickup_location_id', 'dropoff_location_id', 'pickup_time', 'status'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $taxi_booking_id = $data["taxi_booking_id"];
    $user_id = $data["user_id"];
    $taxi_id = $data["taxi_id"];
    $pickup_location_id = $data["pickup_location_id"];
    $dropoff_location_id = $data["dropoff_location_id"];
    $pickup_time = $data["pickup_time"];
    $status = $data["status"];

    // Validate inputs
    if (!validate_int($taxi_booking_id) || !validate_int($user_id) || !validate_int($taxi_id) || !validate_int($pickup_location_id) || !validate_int($dropoff_location_id)) {
        send_response(null, "IDs must be integers", 400);
        exit();
    }

    if (!validate_datetime($pickup_time)) {
        send_response(null, "Invalid datetime format for pickup time. Use 'Y-m-d H:i:s'", 400);
        exit();
    }

    if (!validate_booking_status($status)) {
        send_response(null, "Invalid booking status. Use 'confirmed', 'pending', or 'cancelled'", 400);
        exit();
    }

    // Validate if the taxi exists
    $stmt = $conn->prepare('SELECT * FROM Taxis WHERE taxi_id = ?');
    $stmt->bind_param('i', $taxi_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        send_response(null, "Taxi not found", 404);
        exit();
    }

    // Validate if the pickup and dropoff locations exist
    $stmt = $conn->prepare('SELECT * FROM Locations WHERE location_id IN (?, ?)');
    $stmt->bind_param('ii', $pickup_location_id, $dropoff_location_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 2) {
        send_response(null, "One or both locations not found", 404);
        exit();
    }

    // Update the booking
    $stmt = $conn->prepare('UPDATE TaxiBookings SET user_id = ?, taxi_id = ?, pickup_location_id = ?, dropoff_location_id = ?, pickup_time = ?, status = ? WHERE taxi_booking_id = ?;');
    $stmt->bind_param('iiiissi', $user_id, $taxi_id, $pickup_location_id, $dropoff_location_id, $pickup_time, $status, $taxi_booking_id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $stmt = $conn->prepare('
                SELECT tb.*, t.company_name, t.car_type, l1.city_name AS pickup_city, l1.country AS pickup_country, l2.city_name AS dropoff_city, l2.country AS dropoff_country
                FROM TaxiBookings tb
                JOIN Taxis t ON tb.taxi_id = t.taxi_id
                JOIN Locations l1 ON tb.pickup_location_id = l1.location_id
                JOIN Locations l2 ON tb.dropoff_location_id = l2.location_id
                WHERE tb.taxi_booking_id = ?');
            $stmt->bind_param('i', $taxi_booking_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
            send_response(["booking" => $booking], "Taxi booking updated successfully", 200);
        } else {
            send_response(null, "No taxi booking found with the given ID or no changes made", 404);
        }
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
