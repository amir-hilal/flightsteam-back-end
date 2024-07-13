<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM users;');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            echo json_encode(["users" => $users, "status" => "success"]);
        } else {
            echo json_encode(["message" => "No users were found", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error, "status" => "error"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method", "status" => "error"]);
}
?>
