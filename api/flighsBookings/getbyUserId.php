<?php
require "../../config/config.php";
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $id = $_GET['id'];
    $stmt = $conn->prepare('select * from bookings Where user_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        echo json_encode(["bookings" => $bookings]);
    } else {
        echo json_encode(["message" => "no bookings were found"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
