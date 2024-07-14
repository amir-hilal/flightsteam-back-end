<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";

$decoded_token = authenticate_user_or_admin(); // Authenticate user or admin

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $user_id = $decoded_token['role'] === 'admin' ? $_GET['id'] : $decoded_token['user_id'];

    $stmt = $conn->prepare('
        SELECT tb.*, t.company_name, t.car_type, l1.city_name AS pickup_city, l1.country AS pickup_country, l2.city_name AS dropoff_city, l2.country AS dropoff_country
        FROM TaxiBookings tb
        JOIN Taxis t ON tb.taxi_id = t.taxi_id
        JOIN Locations l1 ON tb.pickup_location_id = l1.location_id
        JOIN Locations l2 ON tb.dropoff_location_id = l2.location_id
        WHERE tb.user_id = ?');
    $stmt->bind_param('i', $user_id);
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $taxibookings = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["taxibookings" => $taxibookings, "status" => "success"], "Taxi bookings fetched successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
