<?php
require "../../config/config.php";
require "../utils/validator.php"; // Include the validator
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = $_POST['id'];
    $first_name = $_POST["first_name"];
    $middle_name = $_POST["middle_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phone_number = $_POST["phone_number"];

    // Validate input data
    $required_fields = ['first_name', 'middle_name', 'last_name', 'email', 'password', 'phone_number'];
    foreach ($required_fields as $field) {
        if (!validate_required($$field)) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    // Additional validation
    if (!validate_string($first_name) || !validate_string($last_name)) {
        send_response(null, "First name and last name must be alphabetic strings", 400);
        exit();
    }

    if (!validate_email($email)) {
        send_response(null, "Invalid email address", 400);
        exit();
    }

    if (!validate_password($password)) {
        send_response(null, "Password must contain at least 8 characters, one number, and one special character", 400);
        exit();
    }

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('UPDATE users SET first_name=?, middle_name=?, last_name=?, password=?, phone_number=?, email=? WHERE user_id=?');
    $stmt->bind_param('ssssssi', $first_name, $middle_name, $last_name, $hashed_password, $phone_number, $email, $id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "User updated", "status" => "success"]);
        } else {
            echo json_encode(["message" => "No user found", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
