<?php
require "../../config/config.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM ChatLogs;');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $chatlogs = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["chatlogs" => $chatlogs, "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
