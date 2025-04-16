<?php
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['rider_id'])) {
    header("Location: rider_dashboard.php");
    exit;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    if (
        empty($_POST['fullName']) || 
        empty($_POST['email']) || 
        empty($_POST['phone']) || 
        empty($_POST['cnic']) || 
        empty($_POST['vehicleType']) || 
        empty($_POST['password']) || 
        empty($_POST['confirmPassword'])
    ) {
        header("Location: rider_register.php?error=empty_fields");
        exit;
    }
    
    $fullName = sanitize_input($conn, $_POST['fullName']);
    $email = sanitize_input($conn, $_POST['email']);
    $phone = sanitize_input($conn, $_POST['phone']);
    $cnic = sanitize_input($conn, $_POST['cnic']);
    $vehicleType = sanitize_input($conn, $_POST['vehicleType']);
    $licenseNumber = sanitize_input($conn, $_POST['licenseNumber'] ?? '');
    $vehicleRegNumber = sanitize_input($conn, $_POST['vehicleRegNumber'] ?? '');
    $password = $_POST['password']; // Don't sanitize password before hashing
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate email format
    if (!is_valid_email($email)) {
        header("Location: rider_register.php?error=invalid_email");
        exit;
    }
    
    // Validate phone number (Pakistan format)
    if (!is_valid_phone($phone)) {
        header("Location: rider_register.php?error=invalid_phone");
        exit;
    }
    
    // Validate CNIC (13 digits)
    if (!preg_match('/^[0-9]{13}$/', $cnic)) {
        header("Location: rider_register.php?error=invalid_cnic");
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        header("Location: rider_register.php?error=password_mismatch");
        exit;
    }
    
    // Check if password meets minimum length
    if (strlen($password) < 6) {
        header("Location: rider_register.php?error=password_short");
        exit;
    }
    
    // Check if email already exists
    $check_email = "SELECT rider_id FROM riders WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: rider_register.php?error=email_exists");
        exit;
    }
    
    // Check if phone already exists
    $check_phone = "SELECT rider_id FROM riders WHERE phone = ?";
    $stmt = $conn->prepare($check_phone);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: rider_register.php?error=phone_exists");
        exit;
    }
    
    // Check if CNIC already exists
    $check_cnic = "SELECT rider_id FROM riders WHERE cnic = ?";
    $stmt = $conn->prepare($check_cnic);
    $stmt->bind_param("s", $cnic);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: rider_register.php?error=cnic_exists");
        exit;
    }
    
    // Hash password for secure storage
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert rider into database
    $insert_query = "INSERT INTO riders (full_name, email, phone, cnic, vehicle_type, license_number, vehicle_reg_number, password, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'offline')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssssss", $fullName, $email, $phone, $cnic, $vehicleType, $licenseNumber, $vehicleRegNumber, $hashed_password);
    
    if ($stmt->execute()) {
        // Get the rider_id of the newly registered rider
        $rider_id = $conn->insert_id;
        
        // Set session variables
        $_SESSION['rider_id'] = $rider_id;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['user_type'] = 'rider';
        
        // Redirect to rider dashboard
        header("Location: rider_dashboard.php");
        exit;
    } else {
        // Registration failed
        header("Location: rider_register.php?error=registration_failed");
        exit;
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to register page
    header("Location: rider_register.php");
    exit;
}

$conn->close();
?>
