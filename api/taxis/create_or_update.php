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
                // Fetch the updated taxi details
                $stmt = $conn->prepare('SELECT * FROM Taxis WHERE taxi_id=?');
                $stmt->bind_param('i', $taxi_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_taxi = $result->fetch_assoc();
                echo json_encode(["message" => "Taxi updated", "status" => "success", "taxi" => $updated_taxi]);
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
            // Fetch the created taxi details
            $taxi_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM Taxis WHERE taxi_id=?');
            $stmt->bind_param('i', $taxi_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_taxi = $result->fetch_assoc();
            echo json_encode(["message" => "New taxi created", "status" => "success", "taxi" => $created_taxi]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
