<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $taxi_booking_id = $_POST["taxi_booking_id"];

    $stmt = $conn->prepare('DELETE FROM TaxiBookings WHERE taxi_booking_id = ?;');
    $stmt->bind_param('i', $taxi_booking_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "taxi booking deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
