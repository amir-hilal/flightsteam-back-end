// api/users/searchByEmail.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
include '../utils/cors.php';

$decoded_token = authenticate_admin(); // Ensure only admins can access

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    if (empty($data['email'])) {
        send_response(null, "Email cannot be null or empty", 400);
        exit();
    }

    $email = $data['email'];

    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?;');
    $stmt->bind_param('s', $email);
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user) {
            send_response(["user" => $user], "User fetched successfully", 200);
        } else {
            send_response(null, "No user found with the given email", 404);
        }
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
