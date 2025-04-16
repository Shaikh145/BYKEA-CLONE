<?php
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    if (
        empty($_POST['fullName']) || 
        empty($_POST['email']) || 
        empty($_POST['phone']) || 
        empty($_POST['password']) || 
        empty($_POST['confirmPassword'])
    ) {
        header("Location: register.php?error=empty_fields");
        exit;
    }
    
    $fullName = sanitize_input($conn, $_POST['fullName']);
    $email = sanitize_input($conn, $_POST['email']);
    $phone = sanitize_input($conn, $_POST['phone']);
    $address = sanitize_input($conn, $_POST['address'] ?? '');
    $password = $_POST['password']; // Don't sanitize password before hashing
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate email format
    if (!is_valid_email($email)) {
        header("Location: register.php?error=invalid_email");
        exit;
    }
    
    // Validate phone number (Pakistan format)
    if (!is_valid_phone($phone)) {
        header("Location: register.php?error=invalid_phone");
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        header("Location: register.php?error=password_mismatch");
        exit;
    }
    
    // Check if password meets minimum length
    if (strlen($password) < 6) {
        header("Location: register.php?error=password_short");
        exit;
    }
    
    // Check if email already exists
    $check_email = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: register.php?error=email_exists");
        exit;
    }
    
    // Check if phone already exists
    $check_phone = "SELECT user_id FROM users WHERE phone = ?";
    $stmt = $conn->prepare($check_phone);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: register.php?error=phone_exists");
        exit;
    }
    
    // Hash password for secure storage
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $insert_query = "INSERT INTO users (full_name, email, phone, password, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sssss", $fullName, $email, $phone, $hashed_password, $address);
    
    if ($stmt->execute()) {
        // Get the user_id of the newly registered user
        $user_id = $conn->insert_id;
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['user_type'] = 'user';
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        // Registration failed
        header("Location: register.php?error=registration_failed");
        exit;
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to register page
    header("Location: register.php");
    exit;
}

$conn->close();
?>
