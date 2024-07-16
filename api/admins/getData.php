<?php
// api/admins/getData.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
require "../../config/config.php";
//require "../utils/auth_middleware.php";
//$admin = authenticate_admin(); // Allow all admins to get all admin accounts

// Example query to get stats
$totalBookingsQuery = "SELECT COUNT(*) as total_bookings FROM bookings";
$pendingBookingsQuery = "SELECT COUNT(*) as pending_bookings FROM bookings WHERE status = 'pending'";
$totalUsersQuery = "SELECT COUNT(*) as total_users FROM users";

// Execute queries
$totalBookingsResult = $conn->query($totalBookingsQuery);
$pendingBookingsResult = $conn->query($pendingBookingsQuery);
$totalUsersResult = $conn->query($totalUsersQuery);

// Fetch results
$totalBookings = $totalBookingsResult->fetch_assoc()['total_bookings'];
$pendingBookings = $pendingBookingsResult->fetch_assoc()['pending_bookings'];
$totalUsers = $totalUsersResult->fetch_assoc()['total_users'];

// Create array with results
$response = [
    'totalBookings' => $totalBookings,
    'pendingBookings' => $pendingBookings,
    'totalUsers' => $totalUsers
];

// Output JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close connection
$conn->close();
?>
