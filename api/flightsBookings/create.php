<?php
require "../../config/config.php";
require "../utils/response.php";
include '../utils/cors.php';
require "../utils/jwt.php"; // Include the JWT validation functions

if ($_SERVER['REQUEST_METHOD'] == "POST") {
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

    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['seats', 'flight_id'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $seats = $data['seats'];
    $flight_id = $data['flight_id'];

    $stmt = $conn->prepare('INSERT INTO bookings (user_id, flight_id, booking_date, status) VALUES (?, ?, CURRENT_TIMESTAMP, "confirmed")');

    if ($stmt === false) {
        send_response(null, "Prepare failed: (" . $conn->errno . ") " . $conn->error, 500);
        exit();
    }

    try {
        $conn->begin_transaction();
        for ($i = 0; $i < $seats; $i++) {
            $stmt->bind_param('ii', $user_id, $flight_id);
            $stmt->execute();
        }
        $conn->commit();

        // Fetching detailed booking information
        $query = "
            SELECT
                b.booking_id,
                b.booking_date,
                b.status,
                f.flight_id,
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
                aa.city_code AS arrival_city_code
            FROM bookings b
             JOIN Flights f ON b.flight_id = f.flight_id
             JOIN Locations da ON f.departure_airport_id = da.location_id
             JOIN Locations aa ON f.arrival_airport_id = aa.location_id
            WHERE b.user_id = ? AND b.flight_id = ?
            ORDER BY b.booking_id DESC
            LIMIT ?
        ";

        $details_stmt = $conn->prepare($query);
        $limit = $seats;
        $details_stmt->bind_param('iii', $user_id, $flight_id, $limit);
        $details_stmt->execute();
        $result = $details_stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);

        send_response(["bookings" => $bookings, "status"=>"success","message" => "New Booking Created"], "Booking successful", 201);
    } catch (Exception $e) {
        $conn->rollback();
        send_response(null, "Booking failed: " . $e->getMessage(), 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
