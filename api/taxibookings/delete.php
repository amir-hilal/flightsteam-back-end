<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
include '../utils/cors.php';

$decoded_token = authenticate_admin(); // Authenticate admin

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $taxi_booking_id = $data["taxi_booking_id"];

    $stmt = $conn->prepare('DELETE FROM TaxiBookings WHERE taxi_booking_id = ?;');
    $stmt->bind_param('i', $taxi_booking_id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            send_response(["message" => "Taxi booking deleted", "status" => "success"], "Taxi booking deleted successfully", 200);
        } else {
            send_response(null, "No taxi booking found with the given ID", 404);
        }
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
