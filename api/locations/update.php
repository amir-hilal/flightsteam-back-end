<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $location_id = $_POST["location_id"];
    $city_name = $_POST["city_name"];
    $longitude = $_POST["longitude"];
    $latitude = $_POST["latitude"];
    $country = $_POST["country"];
    $city_code = $_POST["city_code"];

    $stmt = $conn->prepare('UPDATE Locations SET city_name = ?, longitude = ?, latitude = ?, country = ?, city_code = ? WHERE location_id = ?;');
    $stmt->bind_param('sddssi', $city_name, $longitude, $latitude, $country, $city_code, $location_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "location updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
