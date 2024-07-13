// api/locations/create_or_update.php
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

    $city_name = $data["city_name"];
    $longitude = $data["longitude"];
    $latitude = $data["latitude"];
    $country = $data["country"];
    $city_code = $data["city_code"];

    if (isset($data['location_id'])) {
        // Update existing location
        $location_id = $data['location_id'];
        $stmt = $conn->prepare('UPDATE Locations SET city_name = ?, longitude = ?, latitude = ?, country = ?, city_code = ? WHERE location_id = ?;');
        $stmt->bind_param('sddssi', $city_name, $longitude, $latitude, $country, $city_code, $location_id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(["message" => "Location updated", "status" => "success"]);
            } else {
                echo json_encode(["message" => "No location found with the given ID or no changes made", "status" => "error"]);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        // Create new location
        $stmt = $conn->prepare('INSERT INTO Locations (city_name, longitude, latitude, country, city_code) VALUES (?, ?, ?, ?, ?);');
        $stmt->bind_param('sddss', $city_name, $longitude, $latitude, $country, $city_code);
        try {
            $stmt->execute();
            echo json_encode(["message" => "New location created", "status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
