<?php
require "../../config/config.php";
require "../utils/response.php";
require "../utils/send_verification_email.php";
require "../utils/validator.php";
include '../utils/cors.php';
date_default_timezone_set('Asia/Beirut');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $required_fields = ['first_name', 'middle_name', 'last_name', 'email', 'password', 'phone_number'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    if (!validate_string($data['first_name']) || !validate_string($data['last_name'])) {
        send_response(null, "First name and last name must be alphabetic strings", 400);
        exit();
    }

    if (!validate_email($data['email'])) {
        send_response(null, "Invalid email address", 400);
        exit();
    }

    if (!validate_password($data['password'])) {
        send_response(null, "Password must contain at least 8 characters, one number, and one special character", 400);
        exit();
    }

    $first_name = $data['first_name'];
    $middle_name = $data['middle_name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $phone_number = $data['phone_number'];

    $verification_code = rand(100000, 999999);
    $created_at = date('Y-m-d H:i:s');
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $conn->prepare('SELECT * FROM Users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        send_response(null, 'Email is already registered', 400);
        exit();
    }

    $stmt = $conn->prepare('SELECT * FROM UserVerifications WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare('UPDATE UserVerifications SET first_name = ?, middle_name = ?, last_name = ?, password = ?, phone_number = ?, verification_code = ?, created_at = ?, expires_at = ? WHERE email = ?');
        $stmt->bind_param('ssssssiss', $first_name, $middle_name, $last_name, $password, $phone_number, $verification_code, $created_at, $expires_at, $email);
    } else {
        $stmt = $conn->prepare('INSERT INTO UserVerifications (first_name, middle_name, last_name, email, password, phone_number, verification_code, created_at, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssiss', $first_name, $middle_name, $last_name, $email, $password, $phone_number, $verification_code, $created_at, $expires_at);
    }

    try {
        $stmt->execute();
        if (send_verification_email($email, $verification_code)) {
            send_response(['email' => $email], 'Verification code sent to your email', 200);
        } else {
            send_response(null, 'Failed to send verification email', 500);
        }
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
