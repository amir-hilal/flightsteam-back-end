<?php
require "../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = $_POST['id'];
    $name = $_POST["name"];
    $location = $_POST["location"];
    $code = $_POST["code"];

    // Correct the SQL statement
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
