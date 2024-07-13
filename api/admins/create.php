<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $first_name = $_POST["first_name"];
    $middle_name = $_POST["middle_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $stmt = $conn->prepare('INSERT INTO AdminAccounts (first_name, middle_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?);');
    $stmt->bind_param('ssssss', $first_name, $middle_name, $last_name, $email, $password, $role);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new admin account created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
