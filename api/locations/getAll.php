<?php
require "../../config/config.php";
include '../utils/cors.php';
require "../utils/response.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM Locations;');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $locations = $result->fetch_all(MYSQLI_ASSOC);
        // echo json_encode(["locations" => $locations, "status" => "success"]);
        send_response(["locations" => $locations, "status" => "success"], "Locations fetched successfully", 200);

    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
