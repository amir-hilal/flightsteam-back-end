<!-- // api/admins/login.php -->
<?php
require "../../config/config.php";
require "../utils/jwt.php";
require "../utils/response.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if the JSON decoding was successful
    if ($data === null) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    $email = $data["email"];
    $password = $data["password"];

    // Debugging: Check if email and password are set
    error_log("Email: " . $email);
    error_log("Password: " . $password);

    $stmt = $conn->prepare('SELECT * FROM AdminAccounts WHERE email = ?;');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Debugging: Check if the admin is fetched
    if ($admin) {
        error_log("Admin found: " . json_encode($admin));
    } else {
        error_log("Admin not found for email: " . $email);
    }

    // Debugging: Check if password_verify passes
    if ($admin && password_verify($password, $admin['password'])) {
        error_log("Password verification successful");
        $token = generate_jwt_token(['admin_id' => $admin['admin_id'], 'role' => 'admin']);
        send_response(['token' => $token], 'Login successful', 200);
    } else {
        error_log("Invalid credentials for email: " . $email);
        send_response(null, 'Invalid credentials', 401);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
