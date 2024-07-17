<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/validator.php";
require "../utils/response.php";
include '../utils/cors.php';

$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        send_response(null, "Invalid JSON input", 400);
        exit();
    }

    $required_fields = ['name'];
    foreach ($required_fields as $field) {
        if (!validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $name = $data["name"];

    if (!validate_string($name)) {
        send_response(null, "Name must be a string", 400);
        exit();
    }

    if (isset($data['id'])) {
        // Update existing company
        $id = $data['id'];
        $stmt = $conn->prepare('UPDATE companies SET name=? WHERE company_id=?');
        $stmt->bind_param('si', $name, $id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Fetch the updated company details
                $stmt = $conn->prepare('SELECT * FROM companies WHERE company_id=?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_company = $result->fetch_assoc();
                send_response(["message" => "Company updated", "status" => "success", "company" => $updated_company], 200);
            } else {
                send_response(null, "No company found with the given ID or no changes made", 404);
            }
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    } else {
        // Create new company
        $stmt = $conn->prepare('INSERT INTO companies (name) VALUES (?)');
        $stmt->bind_param('s', $name);
        try {
            $stmt->execute();
            // Fetch the created company details
            $company_id = $stmt->insert_id;
            $stmt = $conn->prepare('SELECT * FROM companies WHERE company_id=?');
            $stmt->bind_param('i', $company_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $created_company = $result->fetch_assoc();
            send_response(["message" => "New company created", "status" => "success", "company" => $created_company], 200);
        } catch (Exception $e) {
            send_response(null, $stmt->error, 500);
        }
    }
} else {
    send_response(null, "Wrong request method", 405);
}
?>
