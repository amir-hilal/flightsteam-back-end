<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST["name"];
    $location = $_POST["location"];
    $code = $_POST["code"];

    $stmt = $conn->prepare('INSERT INTO airports (name, location, code) VALUES (?, ?, ?);');
    $stmt->bind_param('sss', $name, $location, $code);
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
