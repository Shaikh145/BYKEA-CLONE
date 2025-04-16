<?php
session_start();
require_once 'db.php';

// Check if rider is logged in
if (!isset($_SESSION['rider_id'])) {
    header("Location: rider_login.php");
    exit;
}

// Get rider information
$rider_id = $_SESSION['rider_id'];
$query = "SELECT * FROM riders WHERE rider_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Rider not found in database
    session_destroy();
    header("Location: rider_login.php?error=account_error");
    exit;
}

$rider = $result->fetch_assoc();

// Get active assignment
$active_query = "SELECT b.*, u.full_name AS user_name, u.phone AS user_phone 
                FROM bookings b 
                JOIN users u ON b.user_id = u.user_id 
                WHERE b.rider_id = ? AND b.status IN ('accepted', 'in_progress') 
                ORDER BY b.created_at DESC LIMIT 1";
$stmt_active = $conn->prepare($active_query);
$stmt_active->bind_param("i", $rider_id);
$stmt_active->execute();
$active_result = $stmt_active->get_result();
$active_assignment = $active_result->fetch_assoc();

// Get pending bookings (available for pickup)
$pending_query = "SELECT b.*, u.full_name AS user_name 
                 FROM bookings b 
                 JOIN users u ON b.user_id = u.user_id 
                 WHERE b.rider_id IS NULL AND b.status = 'pending' 
                 ORDER BY b.created_at ASC";
$stmt_pending = $conn->prepare($pending_query);
$stmt_pending->execute();
$pending_bookings = $stmt_pending->get_result();

// Get recent completed rides (limit to 5)
$history_query = "SELECT b.*, u.full_name AS user_name 
                 FROM bookings b 
                 JOIN users u ON b.user_id = u.user_id 
                 WHERE b.rider_id = ? AND b.status = 'completed' 
                 ORDER BY b.updated_at DESC LIMIT 5";
$stmt_history = $conn->prepare($history_query);
$stmt_history->bind_param("i", $rider_id);
$stmt_history->execute();
$completed_history = $stmt_history->get_result();

// Calculate earnings
$earnings_query = "SELECT 
                    SUM(fare) AS total_earnings,
                    COUNT(*) AS total_rides,
                    AVG(fare) AS avg_fare
                  FROM bookings 
                  WHERE rider_id = ? AND status = 'completed'";
$stmt_earnings = $conn->prepare($earnings_query);
$stmt_earnings->bind_param("i", $rider_id);
$stmt_earnings->execute();
$earnings_result = $stmt_earnings->get_result();
$earnings = $earnings_result->fetch_assoc();

// Calculate today's earnings
$today_earnings_query = "SELECT 
                          SUM(fare) AS today_earnings,
                          COUNT(*) AS today_rides
                        FROM bookings 
                        WHERE rider_id = ? AND status = 'completed' 
                        AND DATE(updated_at) = CURDATE()";
$stmt_today = $conn->prepare($today_earnings_query);
$stmt_today->bind_param("i", $rider_id);
$stmt_today->execute();
$today_result = $stmt_today->get_result();
$today_earnings = $today_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard - Bykea</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Internal CSS */
        :root {
            --primary-color: #2fb34a;
            --secondary-color: #1a8b32;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--text-color);
        }
        
        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-left: 10px;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 4px;
            width: 200px;
            padding: 10px 0;
            display: none;
            z-index: 1000;
        }
        
        .user-info:hover .user-dropdown {
            display: block;
        }
        
        .user-dropdown a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text-color);
            transition: background-color 0.3s;
        }
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
    if (empty($_POST['email']) || empty($_POST['password'])) {
        header("Location: login.php?error=empty_fields");
        exit;
    }
    
    $email = sanitize_input($conn, $_POST['email']);
    $password = $_POST['password']; // Don't sanitize password before verification
    
    // Validate email format
    if (!is_valid_email($email)) {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
    
    // Prepare SQL statement to prevent SQL injection
    $query = "SELECT user_id, full_name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password using password_verify - which requires password_hash for storing passwords
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = 'user';
            
            // Handle "remember me" functionality
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                // Set cookies for 30 days
                setcookie('user_email', $email, time() + (30 * 24 * 60 * 60), "/");
                // Note: Never store passwords in cookies, even if encrypted
            }
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            // Invalid password
            header("Location: login.php?error=invalid_credentials");
            exit;
        }
    } else {
        // User not found
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to login page
    header("Location: login.php");
    exit;
}

$conn->close();
?>
