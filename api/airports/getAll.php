<?php
require "../../config/config.php";
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('select * from airports');
    $stmt->execute();
    $result = $stmt->get_result();
    $airports = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $airports[] = $row;
        }
        echo json_encode(["airports" => $airports]);
    } else {
        echo json_encode(["message" => "no airports were found"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
