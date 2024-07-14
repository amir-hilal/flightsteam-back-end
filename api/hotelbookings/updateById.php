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

    $required_fields = ['hotel_booking_id', 'user_id', 'hotel_id', 'check_in_date', 'check_out_date', 'status'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $hotel_booking_id = $data["hotel_booking_id"];
    $user_id = $data["user_id"];
    $hotel_id = $data["hotel_id"];
    $check_in_date = $data["check_in_date"];
    $check_out_date = $data["check_out_date"];
    $status = $data["status"];

    $stmt = $conn->prepare('UPDATE HotelBookings SET user_id = ?, hotel_id = ?, check_in_date = ?, check_out_date = ?, status = ? WHERE hotel_booking_id = ?;');
    $stmt->bind_param('iisssi', $user_id, $hotel_id, $check_in_date, $check_out_date, $status, $hotel_booking_id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $stmt = $conn->prepare('SELECT * FROM HotelBookings WHERE hotel_booking_id = ?');
            $stmt->bind_param('i', $hotel_booking_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
            send_response(["booking" => $booking], "Hotel booking updated successfully", 200);
        } else {
            send_response(null, "No hotel booking found with the given ID or no changes made", 404);
        }
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
