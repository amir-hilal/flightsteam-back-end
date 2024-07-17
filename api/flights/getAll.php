<?php
require "../../config/config.php";
require "../utils/response.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $query = "
        SELECT
            f.flight_id,
            f.flight_number,
            f.company_id,
            c.name AS company_name,
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
        FROM Flights f
        INNER JOIN Locations da ON f.departure_airport_id = da.location_id
        INNER JOIN Locations aa ON f.arrival_airport_id = aa.location_id
        INNER JOIN companies c ON f.company_id = c.company_id
    ";

    $stmt = $conn->prepare($query);

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $flights = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["flights" => $flights], "Flight details retrieved successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
