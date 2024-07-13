<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $flight_id = $_POST["flight_id"];
    $flight_number = $_POST["flight_number"];
    $company_id = $_POST["company_id"];
    $departure_airport_id = $_POST["departure_airport_id"];
    $arrival_airport_id = $_POST["arrival_airport_id"];
    $departure_time = $_POST["departure_time"];
    $arrival_time = $_POST["arrival_time"];
    $price = $_POST["price"];
    $available_seats = $_POST["available_seats"];

    $stmt = $conn->prepare('UPDATE Flights SET flight_number = ?, company_id = ?, departure_airport_id = ?, arrival_airport_id = ?, departure_time = ?, arrival_time = ?, price = ?, available_seats = ? WHERE flight_id = ?;');
    $stmt->bind_param('siiissdii', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats, $flight_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "flight updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
