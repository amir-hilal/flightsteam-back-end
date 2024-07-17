<?php
require "../../config/config.php";
include '../utils/cors.php';
require "../utils/jwt.php"; // Include the JWT validation functions
require "../utils/response.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        send_response(null, "Authorization header not found", 401);
        exit();
    }

    $authHeader = $headers['Authorization'];
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        send_response(null, "Bearer token not found", 401);
        exit();
    }

    $decoded = validate_jwt_token($jwt);

    if (!$decoded) {
        send_response(null, "Invalid token", 401);
        exit();
    }

    $user_id = $decoded['user_id'];

    $query = "
        SELECT
            h.name AS hotel_name,
            l.city_name,
            l.country,
            l.city_code,
            h.price_per_night,
            h.available_rooms,
            hb.check_in_date,
            hb.check_out_date,
            hb.status,
            hb.hotel_booking_id AS booking_id
        FROM hotelbookings hb
        JOIN Hotels h ON hb.hotel_id = h.hotel_id
        JOIN Locations l ON h.location_id = l.location_id
        WHERE hb.user_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["bookings" => $bookings], "Hotel bookings retrieved successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
