<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $taxi_id = $_POST["taxi_id"];
    $company_name = $_POST["company_name"];
    $car_type = $_POST["car_type"];
    $price_per_km = $_POST["price_per_km"];
    $available = $_POST["available"];

    $stmt = $conn->prepare('UPDATE Taxis SET company_name = ?, car_type = ?, price_per_km = ?, available = ? WHERE taxi_id = ?;');
    $stmt->bind_param('ssdii', $company_name, $car_type, $price_per_km, $available, $taxi_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "taxi updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
