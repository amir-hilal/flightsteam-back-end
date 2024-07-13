// api/hotels/delete.php
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

    $hotel_id = $data["hotel_id"];

    $stmt = $conn->prepare('DELETE FROM Hotels WHERE hotel_id = ?;');
    $stmt->bind_param('i', $hotel_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "Hotel deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
