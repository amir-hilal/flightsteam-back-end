<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $hotel_id = $_POST["hotel_id"];
    $name = $_POST["name"];
    $location_id = $_POST["location_id"];
    $price_per_night = $_POST["price_per_night"];
    $available_rooms = $_POST["available_rooms"];

    $stmt = $conn->prepare('UPDATE Hotels SET name = ?, location_id = ?, price_per_night = ?, available_rooms = ? WHERE hotel_id = ?;');
    $stmt->bind_param('sidii', $name, $location_id, $price_per_night, $available_rooms, $hotel_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "hotel updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
