<?php
require_once '../../vendor/autoload.php'; // Ensure this path is correct based on your project structure

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

if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY']);
}
if (!defined('SMTP_EMAIL')) {
    define('SMTP_EMAIL', $_ENV['SMTP_EMAIL']);
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD']);
}
?>
