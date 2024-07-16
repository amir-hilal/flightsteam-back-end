<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
require "../../config/config.php";

function send_response($data, $message) {
    echo json_encode(["data" => $data, "message" => $message]);
}

function validate_required($value) {
    return isset($value) && !empty($value);
}

function validate_flight_number($flight_number) {
    return preg_match('/^[A-Z0-9]+$/i', $flight_number);
}

function validate_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

function validate_datetime($datetime) {
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
    return $d && $d->format('Y-m-d H:i:s') === $datetime;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

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

    if (!validate_flight_number($flight_number)) {
        send_response(null, "Invalid flight number", 400);
        exit();
    }

    if (!validate_int($company_id) || !validate_int($departure_airport_id) || !validate_int($arrival_airport_id) || !validate_int($price) || !validate_int($available_seats)) {
        send_response(null, "Company ID, airport IDs, price, and available seats must be integers", 400);
        exit();
    }

    if (!validate_datetime($departure_time) || !validate_datetime($arrival_time)) {
        send_response(null, "Invalid date and time format. Use 'Y-m-d H:i:s'", 400);
        exit();
    }

    if (isset($data['flight_id'])) {
        $flight_id = $data['flight_id'];
        $stmt = $conn->prepare('UPDATE Flights SET flight_number = ?, company_id = ?, departure_airport_id = ?, arrival_airport_id = ?, departure_time = ?, arrival_time = ?, price = ?, available_seats = ? WHERE flight_id = ?;');
        $stmt->bind_param('siiissdii', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats, $flight_id);
        try {
            $stmt->execute();
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
        $stmt = $conn->prepare('INSERT INTO Flights (flight_number, company_id, departure_airport_id, arrival_airport_id, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
        $stmt->bind_param('siiiisdi', $flight_number, $company_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $price, $available_seats);
        try {
            $stmt->execute();
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
