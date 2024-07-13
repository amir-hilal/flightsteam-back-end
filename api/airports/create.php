<!-- // api/airports/create.php -->
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can create

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $name = $data["name"];
    $location = $data["location"];
    $code = $data["code"];

    $stmt = $conn->prepare('INSERT INTO airports (name, location, code) VALUES (?, ?, ?);');
    $stmt->bind_param('sss', $name, $location, $code);
    try {
        $stmt->execute();
        echo json_encode(["message" => "New airport created", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
