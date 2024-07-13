<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_id = $_POST["user_id"];
    $flight_id = $_POST["flight_id"];
    $status = $_POST["status"];
    $booking_date = $_POST["booking_date"];
    $stmt = $conn->prepare('UPDATE bookings SET flight_id=?, status=?, booking_date=? WHERE user_id=? and flight_id=?');
    $stmt->bind_param('issii', $flight_id, $status, $booking_date, $user_id,$flight_id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "Booking updated", "status" => "success"]);
        } else {
            echo json_encode(["message" => "No booking found with the given user ID or no changes made", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
