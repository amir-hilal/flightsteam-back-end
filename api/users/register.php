<?php
session_start();
require "../../config/config.php";
require "../utils/response.php";
require "../utils/send_verification_email.php";
require "../utils/validator.php"; // Include the validator
date_default_timezone_set('Asia/Beirut'); // Set your timezone

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    $required_fields = ['first_name', 'middle_name', 'last_name', 'email', 'password', 'phone_number'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    // Additional validation
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

    // Assign variables after validation
    $first_name = $data['first_name'];
    $middle_name = $data['middle_name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $phone_number = $data['phone_number'];

    // Generate a 6-digit verification code
    $verification_code = rand(100000, 999999);
    $created_at = date('Y-m-d H:i:s');
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Check if the email is already registered in Users table
    $stmt = $conn->prepare('SELECT * FROM Users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        send_response(null, 'Email is already registered', 400);
        exit();
    }

    // Store user data in session
    $_SESSION['register_data'] = [
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
        'email' => $email,
        'password' => $password,
        'phone_number' => $phone_number
    ];

    // Insert or update verification code in UserVerifications table
    $stmt = $conn->prepare('SELECT * FROM UserVerifications WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email exists, update the verification code and expiration time
        $stmt = $conn->prepare('UPDATE UserVerifications SET verification_code = ?, created_at = ?, expires_at = ? WHERE email = ?');
        $stmt->bind_param('isss', $verification_code, $created_at, $expires_at, $email);
    } else {
        // Email does not exist, insert a new record
        $stmt = $conn->prepare('INSERT INTO UserVerifications (email, verification_code, created_at, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('siss', $email, $verification_code, $created_at, $expires_at);
    }

    try {
        $stmt->execute();
        // Send verification email
        if (send_verification_email($email, $verification_code)) {
            send_response(null, 'Verification code sent to your email', 200);
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
