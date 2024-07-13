<?php
require '../../vendor/autoload.php'; // Ensure this path is correct based on your project structure

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$servername = "localhost";
$username = "root";
$password = '';
$db_name = 'flightsteam';

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY']);
