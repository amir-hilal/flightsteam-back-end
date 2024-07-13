<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST["name"];
    $location_id = $_POST["location_id"];
    $price_per_night = $_POST["price_per_night"];
    $available_rooms = $_POST["available_rooms"];

    $stmt = $conn->prepare('INSERT INTO Hotels (name, location_id, price_per_night, available_rooms) VALUES (?, ?, ?, ?);');
    $stmt->bind_param('sidi', $name, $location_id, $price_per_night, $available_rooms);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new hotel created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
