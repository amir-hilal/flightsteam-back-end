<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/validator.php";
require "../utils/response.php";
include '../utils/cors.php';

$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['city_name', 'longitude', 'latitude', 'country', 'city_code'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $city_name = $data["city_name"];
    $longitude = $data["longitude"];
    $latitude = $data["latitude"];
    $country = $data["country"];
    $city_code = $data["city_code"];

    if (!validate_string($city_name)) {
        send_response(null, "City name must be a string", 400);
        exit();
    }

    if (!validate_float($longitude)) {
        send_response(null, "Longitude must be a float", 400);
        exit();
    }

    if (!validate_float($latitude)) {
        send_response(null, "Latitude must be a float", 400);
        exit();
    }

    if (!validate_string($country)) {
        send_response(null, "Country must be a string", 400);
        exit();
    }

    if (!validate_code($city_code)) {
        send_response(null, "City code must be 3 uppercase letters and can contain numbers, but at least 1 letter", 400);
        exit();
    }

    if (isset($data['location_id'])) {
        // Update existing location
        $location_id = $data['location_id'];
        $stmt = $conn->prepare('UPDATE Locations SET city_name = ?, longitude = ?, latitude = ?, country = ?, city_code = ? WHERE location_id = ?;');
        $stmt->bind_param('sddssi', $city_name, $longitude, $latitude, $country, $city_code, $location_id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated location details
                $stmt = $conn->prepare('SELECT * FROM Locations WHERE location_id=?');
                $stmt->bind_param('i', $location_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_location = $result->fetch_assoc();
                send_response(["message" => "Location updated", "status" => "success", "location" => $updated_location], 200);
            } else {
                send_response(null, "No location found with the given ID or no changes made", 404);
            }
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        // Create new location
        $stmt = $conn->prepare('INSERT INTO Locations (city_name, longitude, latitude, country, city_code) VALUES (?, ?, ?, ?, ?);');
        $stmt->bind_param('sddss', $city_name, $longitude, $latitude, $country, $city_code);
        try {
            $stmt->execute();
            // Fetch the created location details
            $location_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM Locations WHERE location_id=?');
            $stmt->bind_param('i', $location_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_location = $result->fetch_assoc();
            send_response(["message" => "New location created", "status" => "success", "location" => $created_location], 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
