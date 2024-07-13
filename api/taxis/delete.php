<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $taxi_id = $_POST["taxi_id"];

    $stmt = $conn->prepare('DELETE FROM Taxis WHERE taxi_id = ?;');
    $stmt->bind_param('i', $taxi_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "taxi deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
