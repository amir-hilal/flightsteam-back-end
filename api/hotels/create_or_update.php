// api/hotels/create_or_update.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $name = $data["name"];
    $location_id = $data["location_id"];
    $price_per_night = $data["price_per_night"];
    $available_rooms = $data["available_rooms"];

    if (isset($data['hotel_id'])) {
        // Update existing hotel
        $hotel_id = $data['hotel_id'];
        $stmt = $conn->prepare('UPDATE Hotels SET name = ?, location_id = ?, price_per_night = ?, available_rooms = ? WHERE hotel_id = ?;');
        $stmt->bind_param('sidii', $name, $location_id, $price_per_night, $available_rooms, $hotel_id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated hotel details
                $stmt = $conn->prepare('SELECT * FROM Hotels WHERE hotel_id=?');
                $stmt->bind_param('i', $hotel_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_hotel = $result->fetch_assoc();
                echo json_encode(["message" => "Hotel updated", "status" => "success", "hotel" => $updated_hotel]);
            } else {
                echo json_encode(["message" => "No hotel found with the given ID or no changes made", "status" => "error"]);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        // Create new hotel
        $stmt = $conn->prepare('INSERT INTO Hotels (name, location_id, price_per_night, available_rooms) VALUES (?, ?, ?, ?);');
        $stmt->bind_param('sidi', $name, $location_id, $price_per_night, $available_rooms);
        try {
            $stmt->execute();
            // Fetch the created hotel details
            $hotel_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM Hotels WHERE hotel_id=?');
            $stmt->bind_param('i', $hotel_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_hotel = $result->fetch_assoc();
            echo json_encode(["message" => "New hotel created", "status" => "success", "hotel" => $created_hotel]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
