<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";

$decoded_token = authenticate_admin(); // Authenticate admin

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM TaxiBookings;');
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
