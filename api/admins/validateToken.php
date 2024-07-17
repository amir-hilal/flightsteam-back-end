<?php
require "../../config/config.php";
require "../utils/jwt.php";
require "../utils/response.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if the JSON decoding was successful
    if ($data === null) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    if (!isset($data['token'])) {
        send_response(null, 'Token is required', 400);
        exit();
    }

    $token = $data['token'];

    // Validate the JWT token
    $decoded = validate_jwt_token($token);

    if ($decoded) {
        $admin_id = $decoded['admin_id'];
        $role = $decoded['role'];
        // Optionally, you can fetch the admin details from the database
        $stmt = $conn->prepare('SELECT * FROM AdminAccounts WHERE admin_id = ?');
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin) {
            send_response(['admin_id' => $admin_id, 'role' => $role], 'Token is valid', 200);
        } else {
            send_response(null, 'Admin not found', 404);
        }
    } else {
        send_response(null, 'Invalid token', 401);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
