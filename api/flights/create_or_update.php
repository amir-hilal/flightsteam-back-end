// api/flights/create_or_update.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    // Validate input data
    $required_fields = ['flight_number', 'company_id', 'departure_airport_id', 'arrival_airport_id', 'departure_time', 'arrival_time', 'price', 'available_seats'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            echo json_encode(["error" => "$field cannot be null or empty"]);
            exit();
        }
    }

    $flight_number = $data["flight_number"];
    $company_id = $data["company_id"];
    $departure_airport_id = $data["departure_airport_id"];
    $arrival_airport_id = $data["arrival_airport_id"];
    $departure_time = $data["departure_time"];
    $arrival_time = $data["arrival_time"];
    $price = $data["price"];
    $available_seats = $data["available_seats"];

    if (isset($data['flight_id'])) {
        // Update existing flight
        $flight_id = $data['flight_id'];
        $stmt = $conn->prepare('UPDATE Flights SET flight_number = ?, company_id = ?, departure_airport_id = ?, arrival_airport_id = ?, departure_time = ?, arrival_time = ?, price = ?, available_seats = ? WHERE flight_id = ?;');
        $stmt->bind_param('siiissdii', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats, $flight_id);
        try {
            $stmt->execute();
            echo json_encode(["message" => "Flight updated", "status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        // Create new flight
        $stmt = $conn->prepare('INSERT INTO Flights (flight_number, company_id, departure_airport_id, arrival_airport_id, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
        $stmt->bind_param('siiiisdi', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats);
        try {
            $stmt->execute();
            echo json_encode(["message" => "New flight created", "status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
