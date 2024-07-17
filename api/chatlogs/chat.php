<?php
require '../../vendor/autoload.php'; // Include the autoload file for Composer dependencies

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require '../../config/config.php'; // Database configuration
require '../utils/auth_middleware.php'; // Authentication middleware
require '../utils/response.php'; // Response utility
require '../utils/openai.php'; // OpenAI utility
include '../utils/cors.php'; // CORS configuration

// Check for preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Preflight request response
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    exit(0);
}

$admin_or_user = authenticate_user_or_admin(); // Ensure only authenticated users or admins can send messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if ($data === null || !isset($data['message'])) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    $message = $data['message'];
    $user_id = $admin_or_user['user_id']; // Assuming the `authenticate_user_or_admin` function returns an array with user info

    // Log incoming request
    error_log('Received message: ' . $message . ' from user ID: ' . $user_id);

    // Send message to OpenAI and get the response
    $openai_response = get_openai_response($message);

    if (!$openai_response) {
        send_response(null, 'Failed to get response from OpenAI', 500);
        exit();
    }

    // Save chat log in the database
    $stmt = $conn->prepare('INSERT INTO chatlogs (user_id, message, sender, timestamp) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $user_id, $message, 'user', date('Y-m-d H:i:s'));
    $stmt->execute();

    $stmt = $conn->prepare('INSERT INTO chatlogs (user_id, message, sender, timestamp) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $user_id, $openai_response, 'bot', date('Y-m-d H:i:s'));
    $stmt->execute();

    // Return the response to the frontend
    send_response(['response' => $openai_response], 'Success', 200);
} else {
    send_response(null, 'Wrong request method', 405);
}
