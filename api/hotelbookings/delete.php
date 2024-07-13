<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $hotel_booking_id = $_POST["hotel_booking_id"];

    $stmt = $conn->prepare('DELETE FROM HotelBookings WHERE hotel_booking_id = ?;');
    $stmt->bind_param('i', $hotel_booking_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "hotel booking deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
