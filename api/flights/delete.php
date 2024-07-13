// api/flights/delete.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can delete

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $flight_id = $data["flight_id"];

    $stmt = $conn->prepare('DELETE FROM Flights WHERE flight_id = ?;');
    $stmt->bind_param('i', $flight_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "Flight deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
