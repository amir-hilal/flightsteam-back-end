<?php
require "../../config/config.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('
        SELECT t.*, l.city_name, l.country, l.city_code
        FROM Taxis t
        JOIN Locations l ON t.available_location_id = l.location_id
    ');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $taxis = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["taxis" => $taxis, "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
