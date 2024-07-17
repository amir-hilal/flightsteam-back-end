<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/validator.php"; // Include the validator
include '../utils/cors.php';

$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $name = $data["name"];
    $location_id = $data["location_id"];
    $code = $data["code"];

    // Validate inputs
    if (!validate_string($name)) {
        echo json_encode(["error" => "Name must be a valid string"]);
        exit();
    }

    if (!validate_int($location_id)) {
        echo json_encode(["error" => "Location ID must be an integer"]);
        exit();
    }

    if (!validate_code($code)) {
        echo json_encode(["error" => "Code must be 3 uppercase letters and can contain numbers, but at least 1 character"]);
        exit();
    }

    if (isset($data['id'])) {
        // Update existing airport
        $id = $data['id'];
        $stmt = $conn->prepare('UPDATE airports SET name=?, location_id=?, code=? WHERE airport_id=?');
        $stmt->bind_param('sisi', $name, $location_id, $code, $id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated airport details
                $stmt = $conn->prepare('SELECT * FROM airports WHERE airport_id=?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_airport = $result->fetch_assoc();
                echo json_encode(["message" => "Airport updated", "status" => "success", "airport" => $updated_airport]);
            } else {
                echo json_encode(["message" => "No airport found with the given ID or no changes made", "status" => "error"]);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        // Create new airport
        $stmt = $conn->prepare('INSERT INTO airports (name, location_id, code) VALUES (?, ?, ?);');
        $stmt->bind_param('sis', $name, $location_id, $code);
        try {
            $stmt->execute();
            // Fetch the created airport details
            $airport_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM airports WHERE airport_id=?');
            $stmt->bind_param('i', $airport_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_airport = $result->fetch_assoc();
            echo json_encode(["message" => "New airport created", "status" => "success", "airport" => $created_airport]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
