// api/companies/create_or_update.php
<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
$admin = authenticate_admin(); // Ensure only admins can create or update

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit();
    }

    $name = $data["name"];

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
                echo json_encode(["message" => "Company updated", "status" => "success", "company" => $updated_company]);
            } else {
                echo json_encode(["message" => "No company found with the given ID or no changes made", "status" => "error"]);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
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
            echo json_encode(["message" => "New company created", "status" => "success", "company" => $created_company]);
        } catch (Exception $e) {
            echo json_encode(["error" => $stmt->error]);
        }
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
