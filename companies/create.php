<?php
require "../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST["name"];
    $stmt = $conn->prepare('INSERT INTO companies (name) value(?)');
    $stmt->bind_param('s', $name);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new company created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>