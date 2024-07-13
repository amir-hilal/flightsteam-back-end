// utils/response.php
<?php
function send_response($data, $message, $status_code) {
    header("Content-Type: application/json");
    http_response_code($status_code);
    echo json_encode([
        'message' => $message,
        'status' => $status_code,
        'data' => $data
    ]);
    exit();
}
?>
