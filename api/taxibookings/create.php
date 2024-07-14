<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";

$decoded_token = authenticate_user_or_admin(); // Authenticate user or admin

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['taxi_id', 'pickup_location_id', 'dropoff_location_id', 'pickup_time', 'status'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $user_id = $decoded_token['user_id'];
    $taxi_id = $data["taxi_id"];
    $pickup_location_id = $data["pickup_location_id"];
    $dropoff_location_id = $data["dropoff_location_id"];
    $pickup_time = $data["pickup_time"];
    $status = $data["status"];

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

    // Insert the booking
    $stmt = $conn->prepare('INSERT INTO TaxiBookings (user_id, taxi_id, pickup_location_id, dropoff_location_id, pickup_time, status) VALUES (?, ?, ?, ?, ?, ?);');
    $stmt->bind_param('iiiiss', $user_id, $taxi_id, $pickup_location_id, $dropoff_location_id, $pickup_time, $status);
    try {
        $stmt->execute();
        $booking_id = $stmt->insert_id;
        $stmt = $conn->prepare('
            SELECT tb.*, t.company_name, t.car_type, l1.city_name AS pickup_city, l1.country AS pickup_country, l2.city_name AS dropoff_city, l2.country AS dropoff_country
            FROM TaxiBookings tb
            JOIN Taxis t ON tb.taxi_id = t.taxi_id
            JOIN Locations l1 ON tb.pickup_location_id = l1.location_id
            JOIN Locations l2 ON tb.dropoff_location_id = l2.location_id
            WHERE tb.taxi_booking_id = ?');
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        send_response(["booking" => $booking], "New taxi booking created successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
