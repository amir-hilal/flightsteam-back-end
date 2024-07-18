<?php
require "../../config/config.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('
        SELECT
            h.hotel_id,
            h.name,
            h.price_per_night,
            h.available_rooms,
            l.city_name,
            l.country,
            l.city_code
        FROM Hotels h
         JOIN Locations l ON h.location_id = l.location_id;
    ');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $hotels = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["hotels" => $hotels, "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
