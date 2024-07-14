<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";

$decoded_token = authenticate_admin(); // Authenticate admin

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['taxi_booking_id', 'user_id', 'taxi_id', 'pickup_location_id', 'dropoff_location_id', 'pickup_time', 'status'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
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

    $stmt = $conn->prepare('UPDATE TaxiBookings SET user_id = ?, taxi_id = ?, pickup_location_id = ?, dropoff_location_id = ?, pickup_time = ?, status = ? WHERE taxi_booking_id = ?;');
    $stmt->bind_param('iiiissi', $user_id, $taxi_id, $pickup_location_id, $dropoff_location_id, $pickup_time, $status, $taxi_booking_id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $stmt = $conn->prepare('SELECT * FROM TaxiBookings WHERE taxi_booking_id = ?');
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
