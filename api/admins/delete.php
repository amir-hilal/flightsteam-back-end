<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $admin_id = $_POST["admin_id"];

    $stmt = $conn->prepare('DELETE FROM AdminAccounts WHERE admin_id = ?;');
    $stmt->bind_param('i', $admin_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "admin account deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
