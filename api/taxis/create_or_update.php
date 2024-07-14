<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/validator.php";
require "../utils/response.php";

$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['company_name', 'car_type', 'price_per_km', 'available'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $company_name = $data["company_name"];
    $car_type = $data["car_type"];
    $price_per_km = $data["price_per_km"];
    $available = $data["available"];

    if (!validate_string($company_name)) {
        send_response(null, "Company name must be a string", 400);
        exit();
    }

    if (!validate_string($car_type)) {
        send_response(null, "Car type must be a string", 400);
        exit();
    }

    if (!validate_int($price_per_km)) {
        send_response(null, "Price per km must be an integer", 400);
        exit();
    }

    if (!is_bool($available)) {
        send_response(null, "Available must be a boolean", 400);
        exit();
    }

    if (isset($data['taxi_id'])) {
        // Update existing taxi
        $taxi_id = $data['taxi_id'];
        $stmt = $conn->prepare('UPDATE Taxis SET company_name = ?, car_type = ?, price_per_km = ?, available = ? WHERE taxi_id = ?;');
        $stmt->bind_param('ssdii', $company_name, $car_type, $price_per_km, $available, $taxi_id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated taxi details
                $stmt = $conn->prepare('SELECT * FROM Taxis WHERE taxi_id=?');
                $stmt->bind_param('i', $taxi_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_taxi = $result->fetch_assoc();
                send_response(["message" => "Taxi updated", "status" => "success", "taxi" => $updated_taxi], 200);
            } else {
                send_response(null, "No taxi found with the given ID or no changes made", 404);
            }
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        // Create new taxi
        $stmt = $conn->prepare('INSERT INTO Taxis (company_name, car_type, price_per_km, available) VALUES (?, ?, ?, ?);');
        $stmt->bind_param('ssdi', $company_name, $car_type, $price_per_km, $available);
        try {
            $stmt->execute();
            // Fetch the created taxi details
            $taxi_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM Taxis WHERE taxi_id=?');
            $stmt->bind_param('i', $taxi_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_taxi = $result->fetch_assoc();
            send_response(["message" => "New taxi created", "status" => "success", "taxi" => $created_taxi], 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
