<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require "../../config/config.php";
require "../utils/response.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $query = "
        SELECT
            location_id,
            city_name,
            country,
            city_code
        FROM Locations
    ";

    $stmt = $conn->prepare($query);

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $locations = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["locations" => $locations], "Locations retrieved successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>

