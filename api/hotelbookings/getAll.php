<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
include '../utils/cors.php';

$decoded_token = authenticate_admin(); // Authenticate admin

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM HotelBookings;');
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
