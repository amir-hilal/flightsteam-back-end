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
            t.company_name,
            t.car_type,
            t.price_per_km,
            t.available,
            pl.city_name AS pickup_city,
            pl.country AS pickup_country,
            pl.city_code AS pickup_city_code,
            dl.city_name AS dropoff_city,
            dl.country AS dropoff_country,
            dl.city_code AS dropoff_city_code,
            tb.pickup_time,
            tb.status,
            tb.taxi_booking_id AS booking_id
        FROM taxibookings tb
        JOIN Taxis t ON tb.taxi_id = t.taxi_id
        JOIN Locations pl ON tb.pickup_location_id = pl.location_id
        JOIN Locations dl ON tb.dropoff_location_id = dl.location_id
        WHERE tb.user_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["bookings" => $bookings], "Taxi bookings retrieved successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
