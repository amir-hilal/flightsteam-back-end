<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
include '../utils/cors.php';

$decoded_token = authenticate_user_or_admin(); // Ensure only authenticated users or admins can access

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $user_id = $decoded_token['role'] === 'admin' ? $_GET['id'] : $decoded_token['user_id'];

    $stmt = $conn->prepare('
        SELECT
            b.booking_id,
            b.user_id,
            b.flight_id,
            b.status,
            b.booking_date,
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
            da.city_code AS departure_code,
            aa.city_name AS arrival_city,
            aa.country AS arrival_country,
            aa.city_code AS arrival_code
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        JOIN locations da ON f.departure_airport_id = da.location_id
        JOIN locations aa ON f.arrival_airport_id = aa.location_id
        WHERE b.user_id = ?
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        send_response(["bookings" => $bookings], 'Bookings fetched successfully', 200);
    } else {
        send_response(null, 'No bookings were found', 404);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
