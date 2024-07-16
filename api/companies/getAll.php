<?php
require "../../config/config.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM companies');
    $stmt->execute();
    $result = $stmt->get_result();
    $companies = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $companies[] = $row;
        }
        echo json_encode(["companies" => $companies]);
    } else {
        echo json_encode(["message" => "No companies were found"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
