<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $stmt = $conn->prepare('SELECT message, sender FROM chatlogs WHERE user_id = ?;');
        $stmt->bind_param('i', $user_id);
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            $chatlogs = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(["chatlogs" => $chatlogs, "status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        echo json_encode(["error" => "User ID not provided"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
