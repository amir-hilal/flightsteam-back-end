<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/validator.php";
require "../utils/response.php";

$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    // Validate input data
    $required_fields = ['flight_number', 'company_id', 'departure_airport_id', 'arrival_airport_id', 'departure_time', 'arrival_time', 'price', 'available_seats'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
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

    // Validate flight_number
    if (!validate_flight_number($flight_number)) {
        send_response(null, "Invalid flight number", 400);
        exit();
    }

    // Validate IDs and price
    if (!validate_int($company_id) || !validate_int($departure_airport_id) || !validate_int($arrival_airport_id) || !validate_int($price) || !validate_int($available_seats)) {
        send_response(null, "Company ID, airport IDs, price, and available seats must be integers", 400);
        exit();
    }

    // Validate times
    if (!validate_datetime($departure_time) || !validate_datetime($arrival_time)) {
        send_response(null, "Invalid date and time format. Use 'Y-m-d H:i:s'", 400);
        exit();
    }

    if (isset($data['flight_id'])) {
        // Update existing flight
        $flight_id = $data['flight_id'];
        $stmt = $conn->prepare('UPDATE Flights SET flight_number = ?, company_id = ?, departure_airport_id = ?, arrival_airport_id = ?, departure_time = ?, arrival_time = ?, price = ?, available_seats = ? WHERE flight_id = ?;');
        $stmt->bind_param('siiissdii', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats, $flight_id);
        try {
            $stmt->execute();
            // Fetch the updated flight details
            $stmt = $conn->prepare('SELECT * FROM Flights WHERE flight_id=?');
            $stmt->bind_param('i', $flight_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $updated_flight = $result->fetch_assoc();
            send_response(["message" => "Flight updated", "status" => "success", "flight" => $updated_flight], 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        // Create new flight
        $stmt = $conn->prepare('INSERT INTO Flights (flight_number, company_id, departure_airport_id, arrival_airport_id, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
        $stmt->bind_param('siiiisdi', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats);
        try {
            $stmt->execute();
            // Fetch the created flight details
            $flight_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM Flights WHERE flight_id=?');
            $stmt->bind_param('i', $flight_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_flight = $result->fetch_assoc();
            send_response(["message" => "New flight created", "status" => "success", "flight" => $created_flight], 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
