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

    $required_fields = ['hotel_id', 'check_in_date', 'check_out_date', 'status'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $user_id = $decoded_token['user_id'];
    $hotel_id = $data["hotel_id"];
    $check_in_date = $data["check_in_date"];
    $check_out_date = $data["check_out_date"];
    $status = $data["status"];

    // Validate if the hotel exists
    $stmt = $conn->prepare('SELECT * FROM Hotels WHERE hotel_id = ?');
    $stmt->bind_param('i', $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        send_response(null, "Hotel not found", 404);
        exit();
    }

    // Insert the booking
    $stmt = $conn->prepare('INSERT INTO HotelBookings (user_id, hotel_id, check_in_date, check_out_date, status) VALUES (?, ?, ?, ?, ?);');
    $stmt->bind_param('iisss', $user_id, $hotel_id, $check_in_date, $check_out_date, $status);
    try {
        $stmt->execute();
        $booking_id = $stmt->insert_id;
        $stmt = $conn->prepare('
            SELECT hb.*, h.name AS hotel_name, h.location_id, l.city_name, l.country, l.city_code
            FROM HotelBookings hb
            JOIN Hotels h ON hb.hotel_id = h.hotel_id
            JOIN Locations l ON h.location_id = l.location_id
            WHERE hb.hotel_booking_id = ?');
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        send_response(["booking" => $booking], "New hotel booking created successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
