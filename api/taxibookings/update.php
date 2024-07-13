<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $taxi_booking_id = $_POST["taxi_booking_id"];
    $user_id = $_POST["user_id"];
    $taxi_id = $_POST["taxi_id"];
    $pickup_location_id = $_POST["pickup_location_id"];
    $dropoff_location_id = $_POST["dropoff_location_id"];
    $pickup_time = $_POST["pickup_time"];
    $status = $_POST["status"];

    $stmt = $conn->prepare('UPDATE TaxiBookings SET user_id = ?, taxi_id = ?, pickup_location_id = ?, dropoff_location_id = ?, pickup_time = ?, status = ? WHERE taxi_booking_id = ?;');
    $stmt->bind_param('iiiissi', $user_id, $taxi_id, $pickup_location_id, $dropoff_location_id, $pickup_time, $status, $taxi_booking_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "taxi booking updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
