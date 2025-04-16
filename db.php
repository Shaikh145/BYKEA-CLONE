<?php
// Database connection file
$db_host = "localhost";
$db_name = "dbj51p4tnzpbcx";
$db_user = "uklz9ew3hrop3";
$db_pass = "zyrbspyjlzjb";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// Helper function to sanitize inputs
function sanitize_input($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Helper function to validate email format
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to validate phone number (Pakistan format)
function is_valid_phone($phone) {
    // Pakistan phone number patterns (+92xxxxxxxxxx or 03xxxxxxxxx)
    return preg_match('/^(\+92|0)[0-9]{10}$/', $phone);
}

// Helper function to calculate distance between two points using Google Maps API
function calculate_distance($origin, $destination) {
    // Note: In a production app, this would use Google Maps Distance Matrix API
    // For this example, we'll use a simplified calculation
    return rand(3, 15); // Return random distance between 3 and 15 km
}

// Helper function to calculate fare based on distance and service type
function calculate_fare($distance, $service_type) {
    $base_fare = 50; // Base fare in PKR
    $per_km_rate = 0;
    
    switch($service_type) {
        case 'ride':
            $per_km_rate = 15;
            break;
        case 'delivery':
            $per_km_rate = 20;
            break;
        case 'food':
            $per_km_rate = 25;
            break;
        default:
            $per_km_rate = 15;
    }
    
    return $base_fare + ($distance * $per_km_rate);
}

// Helper function to generate a booking reference
function generate_booking_reference() {
    return 'BYK' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}
?>
