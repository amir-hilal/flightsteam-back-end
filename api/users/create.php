<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $first_name = $_POST["first_name"];
    $middle_name = $_POST["middle_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phone_number = $_POST["phone_number"];

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('INSERT INTO users (first_name, middle_name, last_name, email, password, phone_number) VALUES (?, ?, ?, ?, ?, ?);');
    $stmt->bind_param('ssssss', $first_name, $middle_name, $last_name, $email, $hashed_password, $phone_number);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new user created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>