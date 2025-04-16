[<?php
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
    if (empty($_POST['email']) || empty($_POST['password'])) {
        header("Location: rider_login.php?error=empty_fields");
        exit;
    }
    
    $email = sanitize_input($conn, $_POST['email']);
    $password = $_POST['password']; // Don't sanitize password before verification
    
    // Validate email format
    if (!is_valid_email($email)) {
        header("Location: rider_login.php?error=invalid_credentials");
        exit;
    }
    
    // Prepare SQL statement to prevent SQL injection
    $query = "SELECT rider_id, full_name, password FROM riders WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $rider = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $rider['password'])) {
            // Set session variables
            $_SESSION['rider_id'] = $rider['rider_id'];
            $_SESSION['full_name'] = $rider['full_name'];
            $_SESSION['user_type'] = 'rider';
            
            // Handle "remember me" functionality
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                // Set cookies for 30 days
                setcookie('rider_email', $email, time() + (30 * 24 * 60 * 60), "/");
                // Note: Never store passwords in cookies, even if encrypted
            }
            
            // Update rider status to available
            $update_status = "UPDATE riders SET status = 'available' WHERE rider_id = ?";
            $update_stmt = $conn->prepare($update_status);
            $update_stmt->bind_param("i", $rider['rider_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Redirect to rider dashboard
            header("Location: rider_dashboard.php");
            exit;
        } else {
            // Invalid password
            header("Location: rider_login.php?error=invalid_credentials");
            exit;
        }
    } else {
        // Rider not found
        header("Location: rider_login.php?error=invalid_credentials");
        exit;
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to login page
    header("Location: rider_login.php");
    exit;
}

$conn->close();
?>
