<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $city_name = $_POST["city_name"];
    $longitude = $_POST["longitude"];
    $latitude = $_POST["latitude"];
    $country = $_POST["country"];
    $city_code = $_POST["city_code"];

    $stmt = $conn->prepare('INSERT INTO Locations (city_name, longitude, latitude, country, city_code) VALUES (?, ?, ?, ?, ?);');
    $stmt->bind_param('sddss', $city_name, $longitude, $latitude, $country, $city_code);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new location created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
