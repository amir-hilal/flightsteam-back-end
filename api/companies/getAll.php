<?php
require "../../config/config.php";
include '../utils/cors.php';
require "../utils/response.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM companies');
    $stmt->execute();
    $result = $stmt->get_result();
    $companies = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $companies[] = $row;
        }
        // echo json_encode(["companies" => $companies]);
        send_response(["companies" => $companies, "status" => "success"], "Company details retrieved successfully", 200);

    } else {
        echo json_encode(["message" => "No companies were found"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
