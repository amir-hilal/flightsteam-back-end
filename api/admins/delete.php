// api/admins/delete.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_superadmin(); // Ensure only superadmins can delete

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $admin_id = $_POST["admin_id"];

    $stmt = $conn->prepare('DELETE FROM AdminAccounts WHERE admin_id = ?;');
    $stmt->bind_param('i', $admin_id);
    try {
        $stmt->execute();
        echo json_encode(["message" => "Admin account deleted", "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
