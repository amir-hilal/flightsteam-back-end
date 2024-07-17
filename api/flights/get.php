<?php
require "../../config/config.php";
require "../utils/response.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['id'])) {
        $flight_id = $_GET['id'];

        $query = "
            SELECT
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
            FROM Flights f
            INNER JOIN Locations da ON f.departure_airport_id = da.location_id
            INNER JOIN Locations aa ON f.arrival_airport_id = aa.location_id
            WHERE f.flight_id = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $flight_id);

        try {
            $stmt->execute();
            $result = $stmt->get_result();
            $flight = $result->fetch_assoc();

            if ($flight) {
                send_response(["flight" => $flight], "Flight details retrieved successfully", 200);
            } else {
                send_response(null, "Flight not found", 404);
            }
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        send_response(null, "Flight ID is required", 400);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
