<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $admin_id = $_POST["admin_id"];
    $first_name = $_POST["first_name"];
    $middle_name = $_POST["middle_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $stmt = $conn->prepare('UPDATE AdminAccounts SET first_name = ?, middle_name = ?, last_name = ?, email = ?, password = ?, role = ? WHERE admin_id = ?;');
    $stmt->bind_param('ssssssi', $first_name, $middle_name, $last_name, $email, $password, $role, $admin_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "admin account updated", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
