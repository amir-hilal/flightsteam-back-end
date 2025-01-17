<?php
require "../../config/config.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $chat_id = $_POST["chat_id"];

    $stmt = $conn->prepare('DELETE FROM ChatLogs WHERE chat_id = ?;');
    $stmt->bind_param('i', $chat_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "chat log deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
