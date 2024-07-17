<?php
require "../../config/config.php";
require "../utils/response.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (!isset($_GET['location_id'])) {
        send_response(null, "Location ID is required", 400);
        exit();
    }

    $location_id = $_GET['location_id'];

    $query = "SELECT * FROM Hotels WHERE location_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $location_id);

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $hotels = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["hotels" => $hotels], "Hotels details retrieved successfully", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
