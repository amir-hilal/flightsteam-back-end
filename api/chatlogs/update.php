<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $chat_id = $_POST["chat_id"];
    $user_id = $_POST["user_id"];
    $message = $_POST["message"];
    $sender = $_POST["sender"];

    $stmt = $conn->prepare('UPDATE ChatLogs SET user_id = ?, message = ?, sender = ? WHERE chat_id = ?;');
    $stmt->bind_param('issi', $user_id, $message, $sender, $chat_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "chat log updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
