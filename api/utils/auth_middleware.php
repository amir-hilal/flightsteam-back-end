// utils/auth_middleware.php
<?php
require_once "jwt.php";
require_once "response.php";

function authenticate_admin() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        send_response(null, 'Authorization header not found', 401);
        exit();
    }

    $authHeader = $headers['Authorization'];
    list($jwt) = sscanf($authHeader, 'Bearer %s');
    if (!$jwt) {
        send_response(null, 'Invalid token format', 401);
        exit();
    }

    $decoded = validate_jwt_token($jwt);
    if (!$decoded || ($decoded['role'] !== 'admin' && $decoded['role'] !== 'superadmin')) {
        send_response(null, 'Invalid token or insufficient permissions', 401);
        exit();
    }

    return $decoded;
}

function authenticate_superadmin() {
    $admin = authenticate_admin();
    if ($admin['role'] !== 'superadmin') {
        send_response(null, 'Superadmin permissions required', 403);
        exit();
    }
    return $admin;
}
?>
