<?php
require "../../config/config.php";
require "../utils/response.php";
require "../utils/validator.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $required_fields = ['email', 'verification_code'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $email = $data['email'];
    $verification_code = $data['verification_code'];

    if (!validate_email($email)) {
        send_response(null, "Invalid email address", 400);
        exit();
    }

    $stmt = $conn->prepare('SELECT * FROM UserVerifications WHERE email = ? AND verification_code = ? AND expires_at > NOW()');
    $stmt->bind_param('si', $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $register_data = $result->fetch_assoc();

        $stmt = $conn->prepare('INSERT INTO Users (first_name, middle_name, last_name, email, password, phone_number) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'ssssss',
            $register_data['first_name'],
            $register_data['middle_name'],
            $register_data['last_name'],
            $register_data['email'],
            $register_data['password'],
            $register_data['phone_number']
        );

        try {
            $stmt->execute();
            $stmt = $conn->prepare('DELETE FROM UserVerifications WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();

            send_response(null, 'User successfully verified and created', 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        send_response(null, 'Invalid or expired verification code', 400);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
