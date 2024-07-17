<?php
require "../../config/config.php";
include '../utils/cors.php';
require "../utils/auth_middleware.php";
$admin = authenticate_superadmin(); // Ensure only superadmins can delete

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $admin_id = $data["admin_id"];

    // Fetch the admin account details before deletion
    $stmt = $conn->prepare('SELECT * FROM AdminAccounts WHERE admin_id = ?');
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_to_delete = $result->fetch_assoc();

    if ($admin_to_delete) {
        $stmt = $conn->prepare('DELETE FROM AdminAccounts WHERE admin_id = ?;');
        $stmt->bind_param('i', $admin_id);
        try {
            $stmt->execute();
        send_response(["Deleted User" => $admin_to_delete, "status" => "success"], "Company details retrieved successfully", 200);

            // echo json_encode(["message" => "Admin account deleted", "status" => "success", "admin" => $admin_to_delete]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        echo json_encode(["error" => "Admin account not found", "status" => "error"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
