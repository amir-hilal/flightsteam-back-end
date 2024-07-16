<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require "../../config/config.php";
//require "../utils/auth_middleware.php";
require "../utils/response.php";

//$admin = authenticate_admin(); // Ensure only admins can delete

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $flight_id = $data["flight_id"];

    // Fetch the flight details before deleting
    $stmt = $conn->prepare('SELECT * FROM Flights WHERE flight_id = ?');
    $stmt->bind_param('i', $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $flight_to_delete = $result->fetch_assoc();

    if ($flight_to_delete) {
        $stmt = $conn->prepare('DELETE FROM Flights WHERE flight_id = ?');
        $stmt->bind_param('i', $flight_id);
        try {
            $stmt->execute();
            send_response($flight_to_delete, 'Flight deleted successfully', 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        send_response(null, 'No flight found with the given ID', 404);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
