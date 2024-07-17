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
            f.flight_number,
            f.company_id,
            f.departure_airport_id,
            f.arrival_airport_id,
            f.departure_time,
            f.arrival_time,
            f.price,
            f.available_seats,
            da.city_name AS departure_city,
            da.country AS departure_country,
            da.city_code AS departure_city_code,
            aa.city_name AS arrival_city,
            aa.country AS arrival_country,
            aa.city_code AS arrival_city_code,
            b.booking_date,
            b.status,
            b.booking_id
        FROM bookings b
        JOIN Flights f ON b.flight_id = f.flight_id
        JOIN Locations da ON f.departure_airport_id = da.location_id
        JOIN Locations aa ON f.arrival_airport_id = aa.location_id
        WHERE b.user_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["bookings" => $bookings], "Flight bookings retrieved successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
