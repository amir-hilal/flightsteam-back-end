<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $company_name = $_POST["company_name"];
    $car_type = $_POST["car_type"];
    $price_per_km = $_POST["price_per_km"];
    $available = $_POST["available"];

    $stmt = $conn->prepare('INSERT INTO Taxis (company_name, car_type, price_per_km, available) VALUES (?, ?, ?, ?);');
    $stmt->bind_param('ssdi', $company_name, $car_type, $price_per_km, $available);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new taxi created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
