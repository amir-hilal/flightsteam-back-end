<?php
require "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM TaxiBookings;');
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $taxibookings = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["taxibookings" => $taxibookings, "status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}
?>
