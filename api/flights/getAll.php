<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM Flights;');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $flights = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["flights" => $flights, "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>