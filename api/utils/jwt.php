<?php
require "../../vendor/autoload.php"; // Ensure this path is correct based on your project structure

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generate_jwt_token($payload) {
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600;  // jwt valid for 1 hour from the issued time
    $payload['iat'] = $issuedAt;
    $payload['exp'] = $expirationTime;

    $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256'); // Include the algorithm parameter
    return $jwt;
}

function validate_jwt_token($token) {
    try {
        error_log("JWT Token: " . $token); // Debugging: Log the JWT token
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256')); // Ensure headers are passed correctly
        error_log("Decoded Token: " . print_r($decoded, true)); // Debugging: Log the decoded token
        return (array) $decoded;
    } catch (Exception $e) {
        error_log("JWT Decode Error: " . $e->getMessage()); // Debugging: Log the error message
        return null;
    }
}
?>
