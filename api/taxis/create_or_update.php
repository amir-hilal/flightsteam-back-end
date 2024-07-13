// api/taxis/create_or_update.php
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

    $company_name = $data["company_name"];
    $car_type = $data["car_type"];
    $price_per_km = $data["price_per_km"];
    $available = $data["available"];

    if (isset($data['taxi_id'])) {
        // Update existing taxi
        $taxi_id = $data['taxi_id'];
        $stmt = $conn->prepare('UPDATE Taxis SET company_name = ?, car_type = ?, price_per_km = ?, available = ? WHERE taxi_id = ?;');
        $stmt->bind_param('ssdii', $company_name, $car_type, $price_per_km, $available, $taxi_id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(["message" => "Taxi updated", "status" => "success"]);
            } else {
                echo json_encode(["message" => "No taxi found with the given ID or no changes made", "status" => "error"]);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        // Create new taxi
        $stmt = $conn->prepare('INSERT INTO Taxis (company_name, car_type, price_per_km, available) VALUES (?, ?, ?, ?);');
        $stmt->bind_param('ssdi', $company_name, $car_type, $price_per_km, $available);
        try {
            $stmt->execute();
            echo json_encode(["message" => "New taxi created", "status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
