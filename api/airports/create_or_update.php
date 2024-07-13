// api/airports/create_or_update.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $name = $data["name"];
    $location = $data["location"];
    $code = $data["code"];

    if (isset($data['id'])) {
        // Update existing airport
        $id = $data['id'];
        $stmt = $conn->prepare('UPDATE airports SET name=?, location=?, code=? WHERE airport_id=?');
        $stmt->bind_param('sssi', $name, $location, $code, $id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated airport details
                $stmt = $conn->prepare('SELECT * FROM airports WHERE airport_id=?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_airport = $result->fetch_assoc();
                echo json_encode(["message" => "Airport updated", "status" => "success", "airport" => $updated_airport]);
            } else {
                echo json_encode(["message" => "No airport found with the given ID or no changes made", "status" => "error"]);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        // Create new airport
        $stmt = $conn->prepare('INSERT INTO airports (name, location, code) VALUES (?, ?, ?);');
        $stmt->bind_param('sss', $name, $location, $code);
        try {
            $stmt->execute();
            // Fetch the created airport details
            $airport_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM airports WHERE airport_id=?');
            $stmt->bind_param('i', $airport_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_airport = $result->fetch_assoc();
            echo json_encode(["message" => "New airport created", "status" => "success", "airport" => $created_airport]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
