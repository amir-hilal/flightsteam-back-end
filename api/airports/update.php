<!-- // api/airports/update.php -->
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $id = $data['id'];
    $name = $data["name"];
    $location = $data["location"];
    $code = $data["code"];

    $stmt = $conn->prepare('UPDATE airports SET name=?, location=?, code=? WHERE airport_id=?');
    $stmt->bind_param('sssi', $name, $location, $code, $id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "Airport updated", "status" => "success"]);
        } else {
            echo json_encode(["message" => "No airport found with the given ID or no changes made", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
