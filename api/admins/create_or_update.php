<!-- // api/admins/create_or_update.php -->
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_superadmin(); // Ensure only superadmins can add or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if the JSON decoding was successful
    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    // Validate input data
    $required_fields = ['first_name', 'middle_name', 'last_name', 'email', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            echo json_encode(["error" => "$field cannot be null or empty"]);
            exit();
        }
    }

    $first_name = $data["first_name"];
    $middle_name = $data["middle_name"];
    $last_name = $data["last_name"];
    $email = $data["email"];
    $password = $data["password"];
    $role = $data["role"];

    // Hash the password for storage and comparison
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the user already exists
    $stmt = $conn->prepare('SELECT * FROM AdminAccounts WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_admin = $result->fetch_assoc();

    if ($existing_admin) {
        // User exists, update the information
        $stmt = $conn->prepare('UPDATE AdminAccounts SET first_name = ?, middle_name = ?, last_name = ?, email = ?, password = ?, role = ? WHERE email = ?');
        $stmt->bind_param('sssssss', $first_name, $middle_name, $last_name, $email, $hashed_password, $role, $email);
        try {
            $stmt->execute();
            echo json_encode(["message" => "Admin account updated", "status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    } else {
        // User does not exist, create a new one
        $stmt = $conn->prepare('INSERT INTO AdminAccounts (first_name, middle_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $first_name, $middle_name, $last_name, $email, $hashed_password, $role);
        try {
            $stmt->execute();
            echo json_encode(["message" => "New admin account created", "status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
