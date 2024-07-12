<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST["name"];
    $location_id = $_POST["location_id"];
    $code = $_POST["code"];

    $stmt = $conn->prepare('INSERT INTO airports (name, location_id, code) VALUES (?, ?, ?);');
    $stmt->bind_param('sis', $name, $location_id, $code);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new airport created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>