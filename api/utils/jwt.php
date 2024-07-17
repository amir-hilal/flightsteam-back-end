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
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256')); // Ensure headers are passed correctly
        return (array) $decoded;
    } catch (Exception $e) {
        return null;
    }
}
?>
