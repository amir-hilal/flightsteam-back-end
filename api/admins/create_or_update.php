// api/admins/create_or_update.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
require "../utils/validator.php"; // Include the validator

$admin = authenticate_superadmin(); // Ensure only superadmins can add or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if the JSON decoding was successful
    if ($data === null) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    // Validate input data
    $required_fields = ['first_name', 'middle_name', 'last_name', 'email', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    // Additional validation
    if (!validate_string($data['first_name']) || !validate_string($data['last_name'])) {
        send_response(null, "First name and last name must be alphabetic strings", 400);
        exit();
    }

    if (!validate_email($data['email'])) {
        send_response(null, "Invalid email address", 400);
        exit();
    }

    if (!validate_password($data['password'])) {
        send_response(null, "Password must contain at least 8 characters, one number, and one special character", 400);
        exit();
    }

    $first_name = $data["first_name"];
    $middle_name = $data["middle_name"];
    $last_name = $data["last_name"];
    $email = $data["email"];
    $password = $data["password"];
    $role = $data["role"];

    // Hash the password for storage and comparison
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (isset($data['id'])) {
        // Update existing admin
        $id = $data['id'];
        $stmt = $conn->prepare('UPDATE AdminAccounts SET first_name = ?, middle_name = ?, last_name = ?, email = ?, password = ?, role = ? WHERE admin_id = ?');
        $stmt->bind_param('ssssssi', $first_name, $middle_name, $last_name, $email, $hashed_password, $role, $id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated admin account details
                $stmt = $conn->prepare('SELECT * FROM AdminAccounts WHERE admin_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_admin = $result->fetch_assoc();
                send_response(["message" => "Admin account updated", "status" => "success", "admin" => $updated_admin], "Admin account updated successfully", 200);
            } else {
                send_response(null, "No admin found with the given ID or no changes made", 404);
            }
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        // Create new admin
        $stmt = $conn->prepare('INSERT INTO AdminAccounts (first_name, middle_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $first_name, $middle_name, $last_name, $email, $hashed_password, $role);
        try {
            $stmt->execute();
            // Fetch the created admin account details
            $admin_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM AdminAccounts WHERE admin_id = ?');
            $stmt->bind_param('i', $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_admin = $result->fetch_assoc();
            send_response(["message" => "New admin account created", "status" => "success", "admin" => $created_admin], "New admin account created successfully", 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
