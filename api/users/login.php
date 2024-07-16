<?php
require "../../config/config.php";
require "../utils/jwt.php";
require "../utils/response.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    $required_fields = ['email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $email = $data['email'];
    $password = $data['password'];

    // Check if the email is registered in Users table
    $stmt = $conn->prepare('SELECT * FROM Users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, generate JWT token
        $payload = [
            'user_id' => $user['user_id'],
            'role' => 'user',
            'email' => $user['email']
        ];
        $token = generate_jwt_token($payload);
        send_response(['token' => $token], 'Login successful', 200);
    } else {
        send_response(null, 'Invalid email or password', 401);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
