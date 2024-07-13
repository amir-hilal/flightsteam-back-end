<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $flight_id = $_POST["flight_id"];

    $stmt = $conn->prepare('DELETE FROM Flights WHERE flight_id = ?;');
    $stmt->bind_param('i', $flight_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "flight deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
+