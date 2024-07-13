<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_id = $_POST["user_id"];
    $flight_id=$_POST["flight_id"];
    $status=$_POST["status"];
    $booking_date = $_POST["booking_date"];

    $stmt = $conn->prepare('INSERT INTO bookings (user_id,flight_id,status,booking_date) VALUES ( ?, ?,?,?);');
    $stmt->bind_param('iiss', $user_id, $flight_id,$status,$booking_date);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new booking created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
