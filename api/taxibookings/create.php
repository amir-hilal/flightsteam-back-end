<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_id = $_POST["user_id"];
    $taxi_id = $_POST["taxi_id"];
    $pickup_location_id = $_POST["pickup_location_id"];
    $dropoff_location_id = $_POST["dropoff_location_id"];
    $pickup_time = $_POST["pickup_time"];
    $status = $_POST["status"];

    $stmt = $conn->prepare('INSERT INTO TaxiBookings (user_id, taxi_id, pickup_location_id, dropoff_location_id, pickup_time, status) VALUES (?, ?, ?, ?, ?, ?);');
    $stmt->bind_param('iiiiss', $user_id, $taxi_id, $pickup_location_id, $dropoff_location_id, $pickup_time, $status);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new taxi booking created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
