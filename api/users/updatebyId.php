<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = $_POST['id'];
    $first_name = $_POST["first_name"];
    $middle_name = $_POST["middle_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phone_number = $_POST["phone_number"];

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('UPDATE users SET first_name=?, middle_name=?, last_name=?, password=?, phone_number=?, email=? where user_id=?');
    $stmt->bind_param('ssssssi', $first_name, $middle_name, $last_name, $hashed_password, $phone_number, $email,$id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "User updated", "status" => "success"]);
        } else {
            echo json_encode(["message" => "No user found ", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
