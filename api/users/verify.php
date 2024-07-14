<?phpsession_start();
require "../../config/config.php";
require "../utils/response.php";
require "../utils/validator.php"; // Include the validator

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Log the received data for debugging
    error_log("Received data: " . json_encode($data));

    // Validate input data
    $required_fields = ['email', 'verification_code'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            error_log("$field cannot be null or empty");
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $email = $data['email'];
    $verification_code = $data['verification_code'];

    if (!validate_email($email)) {
        send_response(null, "Invalid email address", 400);
        exit();
    }

    // Log the email and verification code for debugging
    error_log("Email: $email");
    error_log("Verification code: $verification_code");

    // Check if the verification code is correct and not expired
    $stmt = $conn->prepare('SELECT * FROM UserVerifications WHERE email = ? AND verification_code = ? AND expires_at > NOW()');
    $stmt->bind_param('si', $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    // Log the SQL execution result
    error_log("Query result: " . json_encode($result->fetch_assoc()));

    if ($result->num_rows > 0) {
        error_log("Verification successful for email: $email");

        // Verification successful, create user in Users table
        if (isset($_SESSION['register_data'])) {
            $register_data = $_SESSION['register_data'];

            // Log the session data for debugging
            error_log("Session data: " . json_encode($register_data));

            // Use the data stored in session to create the user
            $stmt = $conn->prepare('INSERT INTO Users (first_name, middle_name, last_name, email, password, phone_number) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param(
                'ssssss',
                $register_data['first_name'],
                $register_data['middle_name'],
                $register_data['last_name'],
                $register_data['email'],
                $register_data['password'],
                $register_data['phone_number']
            );

            try {
                $stmt->execute();

                // Log the successful user creation
                error_log("User created successfully for email: $email");

                // Delete the verification record
                $stmt = $conn->prepare('DELETE FROM UserVerifications WHERE email = ?');
                $stmt->bind_param('s', $email);
                $stmt->execute();

                // Clear the session data
                unset($_SESSION['register_data']);

                send_response(null, 'User successfully verified and created', 200);
            } catch (Exception $e) {
                // Log the error
                error_log("Error creating user: " . $e->getMessage());
                send_response(null, $stmt->error, 500);
            }
        } else {
            error_log("Session data not found for email: $email");
            send_response(null, 'Session data not found. Please register again.', 500);
        }
    } else {
        error_log("Invalid or expired verification code for email: $email");
        send_response(null, 'Invalid or expired verification code', 400);
    }
} else {
    error_log("Wrong request method");
    send_response(null, 'Wrong request method', 405);
}
?>
