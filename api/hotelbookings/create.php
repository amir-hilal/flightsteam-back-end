<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_id = $_POST["user_id"];
    $hotel_id = $_POST["hotel_id"];
    $check_in_date = $_POST["check_in_date"];
    $check_out_date = $_POST["check_out_date"];
    $status = $_POST["status"];

    $stmt = $conn->prepare('INSERT INTO HotelBookings (user_id, hotel_id, check_in_date, check_out_date, status) VALUES (?, ?, ?, ?, ?);');
    $stmt->bind_param('iisss', $user_id, $hotel_id, $check_in_date, $check_out_date, $status);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new hotel booking created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
