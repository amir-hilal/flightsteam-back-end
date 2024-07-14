<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";

$decoded_token = authenticate_user_or_admin(); // Authenticate user or admin

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $user_id = $decoded_token['role'] === 'admin' ? $_GET['id'] : $decoded_token['user_id'];

    $stmt = $conn->prepare('
        SELECT hb.*, h.name AS hotel_name, h.location_id, l.city_name, l.country, l.city_code
        FROM HotelBookings hb
        JOIN Hotels h ON hb.hotel_id = h.hotel_id
        JOIN Locations l ON h.location_id = l.location_id
        WHERE hb.user_id = ?');
    $stmt->bind_param('i', $user_id);
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $hotelbookings = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["hotelbookings" => $hotelbookings, "status" => "success"], "Hotel bookings fetched successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
