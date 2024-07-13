<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $flight_number = $_POST["flight_number"];
    $company_id = $_POST["company_id"];
    $departure_airport_id = $_POST["departure_airport_id"];
    $arrival_airport_id = $_POST["arrival_airport_id"];
    $departure_time = $_POST["departure_time"];
    $arrival_time = $_POST["arrival_time"];
    $price = $_POST["price"];
    $available_seats = $_POST["available_seats"];

    $stmt = $conn->prepare('INSERT INTO Flights (flight_number, company_id, departure_airport_id, arrival_airport_id, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
    $stmt->bind_param('siiiisdi', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats);
    try {
        $stmt->execute();
        echo json_encode(["message" => "new flight created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
