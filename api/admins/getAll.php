<?php
require "../../config/config.php";
include '../utils/cors.php';
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Allow all admins to get all admin accounts

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM AdminAccounts;');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $admins = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $admins[] = $row;
            }
            echo json_encode(["admins" => $admins, "status" => "success"]);
        } else {
            echo json_encode(["message" => "No admins were found", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error, "status" => "error"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method", "status" => "error"]);
}
?>
