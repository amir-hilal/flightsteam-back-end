<?php
require "../connection.php";
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('select * from airports');
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(["users" => $users]);
    } else {
        echo json_encode(["message" => "no airports were found"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
