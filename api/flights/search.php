// api/flights/search.php
<?php
require "../../config/config.php";
require "../utils/response.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['departure_location_id', 'arrival_location_id'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $departure_location_id = $data['departure_location_id'];
    $arrival_location_id = $data['arrival_location_id'];
    $departure_date = isset($data['departure_date']) ? $data['departure_date'] : null;
    $arrival_date = isset($data['arrival_date']) ? $data['arrival_date'] : null;

    $query = "
        SELECT
            f.flight_id,
            f.flight_number,
            f.company_id,
            f.departure_airport_id,
            f.arrival_airport_id,
            f.departure_time,
            f.arrival_time,
            f.price,
            f.available_seats,
            da.city_name AS departure_city,
            da.country AS departure_country,
            da.city_code AS departure_city_code,
            aa.city_name AS arrival_city,
            aa.country AS arrival_country,
            aa.city_code AS arrival_city_code
        FROM Flights f
        INNER JOIN Locations da ON f.departure_airport_id = da.location_id
        INNER JOIN Locations aa ON f.arrival_airport_id = aa.location_id
        WHERE f.departure_airport_id = ? AND f.arrival_airport_id = ?
    ";

    $params = [$departure_location_id, $arrival_location_id];

    if ($departure_date) {
        $query .= " AND DATE(f.departure_time) = ?";
        $params[] = $departure_date;
    }

    if ($arrival_date) {
        $query .= " AND DATE(f.arrival_time) = ?";
        $params[] = $arrival_date;
    }

    $stmt = $conn->prepare($query);

    $types = str_repeat('i', count($params));
    if ($departure_date) $types .= 's';
    if ($arrival_date) $types .= 's';

    $stmt->bind_param($types, ...$params);

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $flights = $result->fetch_all(MYSQLI_ASSOC);
        send_response(["flights" => $flights], "Search results", 200);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
