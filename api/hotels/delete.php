<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $hotel_id = $_POST["hotel_id"];

    $stmt = $conn->prepare('DELETE FROM Hotels WHERE hotel_id = ?;');
    $stmt->bind_param('i', $hotel_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "hotel deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
