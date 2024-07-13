<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $location_id = $_POST["location_id"];

    $stmt = $conn->prepare('DELETE FROM Locations WHERE location_id = ?;');
    $stmt->bind_param('i', $location_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "location deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
