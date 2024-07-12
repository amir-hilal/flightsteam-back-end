<?php
require "../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];

    $stmt = $conn->prepare('UPDATE companies SET name=? WHERE company_id=?');
    $stmt->bind_param('si', $name,$id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "company updated", "status" => "success"]);
        } else {
            echo json_encode(["message" => "No company found with the given ID or no changes made", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
