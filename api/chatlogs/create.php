<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_id = $_POST["user_id"];
    $message = $_POST["message"];
    $sender = $_POST["sender"];

    $stmt = $conn->prepare('INSERT INTO ChatLogs (user_id, message, sender) VALUES (?, ?, ?);');
    $stmt->bind_param('iss', $user_id, $message, $sender);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new chat log created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
