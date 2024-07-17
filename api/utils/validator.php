<?php
function validate_required($value) {
    return isset($value) && !empty($value);
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_string($string) {
    return is_string($string) && preg_match('/^[a-zA-Z]+$/', $string);
}
function validate_password($password) {
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,20}$/';
    return preg_match($pattern, $password);
}




function validate_code($value) {
    return preg_match("/^[A-Z0-9]{3}$/", $value) && preg_match("/[A-Z]/", $value);
}

function validate_int($number) {
    return filter_var($number, FILTER_VALIDATE_INT);
}

function validate_date($date) {
    return DateTime::createFromFormat('Y-m-d', $date) !== false;
}

function validate_datetime($datetime) {
    return DateTime::createFromFormat('Y-m-d H:i:s', $datetime) !== false;
}

function validate_booking_status($status) {
    $valid_statuses = ['confirmed', 'pending', 'cancelled'];
    return in_array($status, $valid_statuses);
}

function validate_flight_number($flight_number) {
    return preg_match('/^[A-Z0-9]{1,8}$/', $flight_number) && preg_match('/[A-Z]/', $flight_number);
}
?>
