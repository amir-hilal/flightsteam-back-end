<?php
require "../../config/config.php";
require "../utils/jwt.php";
require "../utils/response.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    $email = $data["email"];
    $password = $data["password"];

    $stmt = $conn->prepare('SELECT * FROM AdminAccounts WHERE email = ?;');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $payload = [
            'admin_id' => $admin['admin_id'], // Use admin_id
            'role' => $admin['role'],         // Include the actual role
            'email' => $admin['email']
        ];
        $token = generate_jwt_token($payload);
        send_response(['token' => $token, 'role' => $admin['role']], 'Login successful', 200);
    } else {
        send_response(null, 'Invalid credentials', 401);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
