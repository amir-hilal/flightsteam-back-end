<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $hotel_booking_id = $_POST["hotel_booking_id"];
    $user_id = $_POST["user_id"];
    $hotel_id = $_POST["hotel_id"];
    $check_in_date = $_POST["check_in_date"];
    $check_out_date = $_POST["check_out_date"];
    $status = $_POST["status"];

    $stmt = $conn->prepare('UPDATE HotelBookings SET user_id = ?, hotel_id = ?, check_in_date = ?, check_out_date = ?, status = ? WHERE hotel_booking_id = ?;');
    $stmt->bind_param('iisssi', $user_id, $hotel_id, $check_in_date, $check_out_date, $status, $hotel_booking_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "hotel booking updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
