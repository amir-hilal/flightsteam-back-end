// api/hotels/create_or_update.php
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

    // Validate input data
    $required_fields = ['name', 'location_id', 'price_per_night', 'available_rooms'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $name = $data["name"];
    $location_id = $data["location_id"];
    $price_per_night = $data["price_per_night"];
    $available_rooms = $data["available_rooms"];

    if (!validate_string($name)) {
        send_response(null, "Name must be a valid string", 400);
        exit();
    }

    if (!validate_int($location_id)) {
        send_response(null, "Location ID must be an integer", 400);
        exit();
    }

    if (!validate_int($price_per_night)) {
        send_response(null, "Price per night must be an integer", 400);
        exit();
    }

    if (!validate_int($available_rooms)) {
        send_response(null, "Available rooms must be an integer", 400);
        exit();
    }

    if (isset($data['hotel_id'])) {
        // Update existing hotel
        $hotel_id = $data['hotel_id'];
        $stmt = $conn->prepare('UPDATE Hotels SET name = ?, location_id = ?, price_per_night = ?, available_rooms = ? WHERE hotel_id = ?;');
        $stmt->bind_param('sidii', $name, $location_id, $price_per_night, $available_rooms, $hotel_id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated hotel details
                $stmt = $conn->prepare('SELECT * FROM Hotels WHERE hotel_id = ?');
                $stmt->bind_param('i', $hotel_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_hotel = $result->fetch_assoc();
                send_response(["hotel" => $updated_hotel], "Hotel updated successfully", 200);
            } else {
                send_response(null, "No hotel found with the given ID or no changes made", 404);
            }
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        // Create new hotel
        $stmt = $conn->prepare('INSERT INTO Hotels (name, location_id, price_per_night, available_rooms) VALUES (?, ?, ?, ?);');
        $stmt->bind_param('sidi', $name, $location_id, $price_per_night, $available_rooms);
        try {
            $stmt->execute();
            // Fetch the created hotel details
            $hotel_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM Hotels WHERE hotel_id = ?');
            $stmt->bind_param('i', $hotel_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_hotel = $result->fetch_assoc();
            send_response(["hotel" => $created_hotel], "New hotel created successfully", 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
