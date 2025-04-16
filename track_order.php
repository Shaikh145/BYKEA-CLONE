<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found in database
    session_destroy();
    header("Location: login.php?error=account_error");
    exit;
}

$user = $result->fetch_assoc();

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$booking_id = $_GET['id'];

// Get booking information
$booking_query = "SELECT b.*, r.full_name as rider_name, r.phone as rider_phone, r.status as rider_status 
                 FROM bookings b 
                 LEFT JOIN riders r ON b.rider_id = r.rider_id 
                 WHERE b.booking_id = ? AND b.user_id = ?";
$stmt_booking = $conn->prepare($booking_query);
$stmt_booking->bind_param("ii", $booking_id, $user_id);
$stmt_booking->execute();
$booking_result = $stmt_booking->get_result();

if ($booking_result->num_rows === 0) {
    // Booking not found or doesn't belong to user
    header("Location: dashboard.php");
    exit;
}

$booking = $booking_result->fetch_assoc();

// Get specific service details based on service type
$service_details = null;

if ($booking['service_type'] == 'delivery') {
    $delivery_query = "SELECT * FROM delivery_details WHERE booking_id = ?";
    $stmt_delivery = $conn->prepare($delivery_query);
    $stmt_delivery->bind_param("i", $booking_id);
    $stmt_delivery->execute();
    $delivery_result = $stmt_delivery->get_result();
    
    if ($delivery_result->num_rows > 0) {
        $service_details = $delivery_result->fetch_assoc();
    }
} elseif ($booking['service_type'] == 'food') {
    $food_query = "SELECT * FROM food_orders WHERE booking_id = ?";
    $stmt_food = $conn->prepare($food_query);
    $stmt_food->bind_param("i", $booking_id);
    $stmt_food->execute();
    $food_result = $stmt_food->get_result();
    
    if ($food_result->num_rows > 0) {
        $service_details = $food_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Bykea</title>
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
        
        .back-button {
            text-decoration: none;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-button:hover {
            color: var(--primary-color);
        }
        
        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 15px 30px;
        }
        
        .booking-header {
            margin-bottom: 30px;
        }
        
        .booking-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .booking-header p {
            color: #666;
        }
        
        .booking-container {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .booking-details {
            flex: 1;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
        }
        
        .map-container {
            flex: 2;
            height: 400px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }
        
        .map-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--light-gray);
            color: #666;
            font-size: 1.2rem;
        }
        
        .section-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 500;
            width: 150px;
            flex-shrink: 0;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .status-accepted {
            background-color: #b8daff;
            color: #004085;
        }
        
        .status-in_progress {
            background-color: #c3e6cb;
            color: #155724;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .tracking-timeline {
            position: relative;
            margin: 30px 0;
            padding-left: 30px;
        }
        
        .timeline-line {
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: var(--border-color);
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: var(--white);
            border: 2px solid var(--primary-color);
        }
        
        .timeline-item.active::before {
            background-color: var(--primary-color);
        }
        
        .timeline-item.completed::before {
            background-color: var(--primary-color);
        }
        
        .timeline-item.pending::before {
            background-color: var(--white);
            border-color: var(--border-color);
        }
        
        .timeline-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .timeline-time {
            font-size: 0.9rem;
            color: #666;
        }
        
        .rider-info {
            background-color: rgba(47, 179, 74, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .rider-info h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .rider-contact {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .rider-contact a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            color: var(--white);
            background-color: var(--primary-color);
            gap: 5px;
            transition: background-color 0.3s;
        }
        
        .rider-contact a:hover {
            background-color: var(--secondary-color);
        }
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            margin-top: 20px;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: var(--white);
            border: none;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .booking-container {
                flex-direction: column;
            }
            
            .map-container {<?php
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

// Get active bookings assigned to rider
$active_bookings_query = "SELECT * FROM bookings WHERE rider_id = ? AND status IN ('accepted', 'in_progress') ORDER BY created_at DESC";
$stmt_active = $conn->prepare($active_bookings_query);
$stmt_active->bind_param("i", $rider_id);
$stmt_active->execute();
$active_bookings = $stmt_active->get_result();

// Get available bookings (pending and no rider assigned)
$available_bookings_query = "SELECT * FROM bookings WHERE status = 'pending' AND rider_id IS NULL ORDER BY created_at ASC";
$stmt_available = $conn->prepare($available_bookings_query);
$stmt_available->execute();
$available_bookings = $stmt_available->get_result();

// Get recent completed bookings (limit to 5)
$history_query = "SELECT * FROM bookings WHERE rider_id = ? AND status = 'completed' ORDER BY created_at DESC LIMIT 5";
$stmt_history = $conn->prepare($history_query);
$stmt_history->bind_param("i", $rider_id);
$stmt_history->execute();
$booking_history = $stmt_history->get_result();

// Get rider status
$status = $rider['status'];
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
        
        .user-dropdown a:hover {
            background-color: var(--light-gray);
        }
        
        .user-dropdown .logout {
            border-top: 1px solid var(--border-color);
            margin-top: 5px;
            color: #e74c3c;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 15px 30px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-header h2 {
            color: var(--primary-color);
        }
        
        .status-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary-color);
        }
        
        input:focus + .slider {
            box-shadow: 0 0 1px var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .status-text {
            font-weight: 500;
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
        }
        
        .section-title {
            margin-bottom: 20px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Active Rides Section */
        .active-rides-section {
            margin-bottom: 40px;
        }
        
        .booking-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .booking-type {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .booking-status {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .status-accepted {
            background-color: #b8daff;
            color: #004085;
        }
        
        .status-in_progress {
            background-color: #c3e6cb;
            color: #155724;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .booking-details {
            margin-bottom: 15px;
        }
        
        .booking-detail {
            display: flex;
            margin-bottom: 8px;
        }
        
        .booking-detail-label {
            width: 120px;
            font-weight: 500;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-success {
            background-color: #28a745;
            color: var(--white);
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: var(--white);
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .no-bookings {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            text-align: center;
        }
        
        .no-bookings i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        /* Recent Activity */
        .history-section {
            margin-bottom: 40px;
        }
        
        .history-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .history-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .history-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(47, 179, 74, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
        }
        
        .history-details h4 {
            margin-bottom: 5px;
        }
        
        .history-time {
            font-size: 0.9rem;
            color: #666;
        }
        
        /* Available Rides Section */
        .available-rides-section {
            margin-bottom: 40px;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-section {
                grid-template-columns: 1fr;
            }
            
            .booking-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .booking-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="nav-container">
            <div class="logo">
                <div class="logo-img">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 40C31.0457 40 40 31.0457 40 20C40 8.9543 31.0457 0 20 0C8.9543 0 0 8.9543 0 20C0 31.0457 8.9543 40 20 40Z" fill="#2FB34A"/>
                        <path d="M28.5 16C28.5 16 26.5 14 23 14C19.5 14 17.5 16 17.5 16M12.5 16C12.5 16 14.5 14 18 14M18 14V26M18 14C18.5 14 19.5 14 20 14C20.5 14 21.5 14 22 14M22 14V26" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <path d="M10 26L30 26" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h1>Bykea</h1>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($rider['full_name'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($rider['full_name']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                    
                    <div class="user-dropdown">
                        <a href="rider_profile.php"><i class="fas fa-user-circle"></i> Profile</a>
                        <a href="ride_history.php?type=rider"><i class="fas fa-history"></i> Ride History</a>
                        <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h2>Rider Dashboard</h2>
            <div class="status-toggle">
                <span class="status-text">Status:</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="statusToggle" <?php echo ($status !== 'offline') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
                <span class="status-text" id="statusText"><?php echo ucfirst($status); ?></span>
            </div>
        </div>
        
        <!-- Stats Section -->
        <section class="stats-section">
            <?php
                // Get today's completed rides count
                $today_start = date('Y-m-d 00:00:00');
                $today_end = date('Y-m-d 23:59:59');
                $today_rides_query = "SELECT COUNT(*) as count FROM bookings WHERE rider_id = ? AND status = 'completed' AND updated_at BETWEEN ? AND ?";
                $stmt_today = $conn->prepare($today_rides_query);
                $stmt_today->bind_param("iss", $rider_id, $today_start, $today_end);
                $stmt_today->execute();
                $today_rides = $stmt_today->get_result()->fetch_assoc()['count'];
                
                // Get total completed rides
                $total_rides_query = "SELECT COUNT(*) as count FROM bookings WHERE rider_id = ? AND status = 'completed'";
                $stmt_total = $conn->prepare($total_rides_query);
                $stmt_total->bind_param("i", $rider_id);
                $stmt_total->execute();
                $total_rides = $stmt_total->get_result()->fetch_assoc()['count'];
                
                // Get average rating
                $avg_rating = number_format($rider['average_rating'], 1);
                
                // Calculate earnings (simplified for this example)
                $today_earnings_query = "SELECT SUM(fare) as total FROM bookings WHERE rider_id = ? AND status = 'completed' AND updated_at BETWEEN ? AND ?";
                $stmt_earnings = $conn->prepare($today_earnings_query);
                $stmt_earnings->bind_param("iss", $rider_id, $today_start, $today_end);
                $stmt_earnings->execute();
                $today_earnings = $stmt_earnings->get_result()->fetch_assoc()['total'] ?: 0;
            ?>
            <div class="stat-card">
                <div class="stat-value"><?php echo $today_rides; ?></div>
                <div class="stat-label">Today's Rides</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_rides; ?></div>
                <div class="stat-label">Total Rides</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $avg_rating; ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">PKR <?php echo number_format($today_earnings); ?></div>
                <div class="stat-label">Today's Earnings</div>
            </div>
        </section>
        
        <!-- Active Rides Section -->
        <section class="active-rides-section">
            <h3 class="section-title"><i class="fas fa-motorcycle"></i> Active Rides</h3>
            
            <?php if ($active_bookings->num_rows > 0): ?>
                <?php while ($booking = $active_bookings->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="booking-type">
                                <?php if ($booking['service_type'] == 'ride'): ?>
                                    <i class="fas fa-motorcycle"></i> Bike Ride
                                <?php elseif ($booking['service_type'] == 'delivery'): ?>
                                    <i class="fas fa-box"></i> Package Delivery
                                <?php elseif ($booking['service_type'] == 'food'): ?>
                                    <i class="fas fa-utensils"></i> Food Delivery
                                <?php endif; ?>
                            </div>
                            <div class="booking-status status-<?php echo $booking['status']; ?>">
                                <?php 
                                $status_text = ucfirst(str_replace('_', ' ', $booking['status']));
                                echo $status_text;
                                ?>
                            </div>
                        </div>
                        
                        <div class="booking-details">
                            <div class="booking-detail">
                                <div class="booking-detail-label">Reference:</div>
                                <div><?php echo htmlspecialchars($booking['booking_reference']); ?></div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">From:</div>
                                <div><?php echo htmlspecialchars($booking['pickup_location']); ?></div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">To:</div>
                                <div><?php echo htmlspecialchars($booking['dropoff_location']); ?></div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">Fare:</div>
                                <div>PKR <?php echo number_format($booking['fare'], 2); ?></div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">Date:</div>
                                <div><?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></div>
                            </div>
                            <?php
                                // Get user details
                                $user_query = "SELECT full_name, phone FROM users WHERE user_id = ?";
                                $stmt_user = $conn->prepare($user_query);
                                $stmt_user->bind_param("i", $booking['user_id']);
                                $stmt_user->execute();
                                $user_result = $stmt_user->get_result();
                                $user = $user_result->fetch_assoc();
                            ?>
                            <div class="booking-detail">
                                <div class="booking-detail-label">Customer:</div>
                                <div><?php echo htmlspecialchars($user['full_name']); ?> (<?php echo htmlspecialchars($user['phone']); ?>)</div>
                            </div>
                            <?php if ($booking['service_type'] == 'delivery'): ?>
                                <?php
                                    // Get delivery details
                                    $delivery_query = "SELECT recipient_name, recipient_phone, package_type FROM delivery_details WHERE booking_id = ?";
                                    $stmt_delivery = $conn->prepare($delivery_query);
                                    $stmt_delivery->bind_param("i", $booking['booking_id']);
                                    $stmt_delivery->execute();
                                    $delivery_result = $stmt_delivery->get_result();
                                    if ($delivery_result->num_rows > 0) {
                                        $delivery = $delivery_result->fetch_assoc();
                                ?>
                                <div class="booking-detail">
                                    <div class="booking-detail-label">Recipient:</div>
                                    <div><?php echo htmlspecialchars($delivery['recipient_name']); ?> (<?php echo htmlspecialchars($delivery['recipient_phone']); ?>)</div>
                                </div>
                                <div class="booking-detail">
                                    <div class="booking-detail-label">Package Type:</div>
                                    <div><?php echo ucwords(str_replace('_', ' ', $delivery['package_type'])); ?></div>
                                </div>
                                <?php } ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="booking-actions">
                            <?php if ($booking['status'] == 'accepted'): ?>
                                <a href="process_booking.php?action=start&id=<?php echo $booking['booking_id']; ?>&rider=<?php echo $rider_id; ?>" class="btn btn-success">Start Ride</a>
                            <?php elseif ($booking['status'] == 'in_progress'): ?>
                                <a href="process_booking.php?action=complete&id=<?php echo $booking['booking_id']; ?>&rider=<?php echo $rider_id; ?>" class="btn btn-success">Complete Ride</a>
                            <?php endif; ?>
                            <a href="track_order.php?id=<?php echo $booking['booking_id']; ?>&type=rider" class="btn btn-outline">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-motorcycle"></i>
                    <h4>No active rides</h4>
                    <p>You don't have any active rides at the moment. Check available rides below.</p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Available Rides Section -->
        <section class="available-rides-section">
            <h3 class="section-title"><i class="fas fa-list"></i> Available Rides</h3>
            
            <?php if ($available_bookings->num_rows > 0): ?>
                <?php while ($booking = $available_bookings->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="booking-type">
                                <?php if ($booking['service_type'] == 'ride'): ?>
                                    <i class="fas fa-motorcycle"></i> Bike Ride
                                <?php elseif ($booking['service_type'] == 'delivery'): ?>
                                    <i class="fas fa-box"></i> Package Delivery
                                <?php elseif ($booking['service_type'] == 'food'): ?>
                                    <i class="fas fa-utensils"></i> Food Delivery
                                <?php endif; ?>
                            </div>
                            <div class="booking-status status-pending">
                                Pending
                            </div>
                        </div>
                        
                        <div class="booking-details">
                            <div class="booking-detail">
                                <div class="booking-detail-label">From:</div>
                                <div><?php echo htmlspecialchars($booking['pickup_location']); ?></div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">To:</div>
                                <div><?php echo htmlspecialchars($booking['dropoff_location']); ?></div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">Distance:</div>
                                <div><?php echo number_format($booking['distance'], 1); ?> km</div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">Fare:</div>
                                <div>PKR <?php echo number_format($booking['fare'], 2); ?></div>
                            </div>
                            <div class="booking-detail">
                                <div class="booking-detail-label">Payment:</div>
                                <div><?php echo ucfirst($booking['payment_method']); ?></div>
                            </div>
                            <?php if ($booking['service_type'] == 'delivery'): ?>
                                <?php
                                    // Get delivery details
                                    $delivery_query = "SELECT package_type FROM delivery_details WHERE booking_id = ?";
                                    $stmt_delivery = $conn->prepare($delivery_query);
                                    $stmt_delivery->bind_param("i", $booking['booking_id']);
                                    $stmt_delivery->execute();
                                    $delivery_result = $stmt_delivery->get_result();
                                    if ($delivery_result->num_rows > 0) {
                                        $delivery = $delivery_result->fetch_assoc();
                                ?>
                                <div class="booking-detail">
                                    <div class="booking-detail-label">Package Type:</div>
                                    <div><?php echo ucwords(str_replace('_', ' ', $delivery['package_type'])); ?></div>
                                </div>
                                <?php } ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="booking-actions">
                            <a href="accept_ride.php?id=<?php echo $booking['booking_id']; ?>&rider=<?php echo $rider_id; ?>" class="btn btn-primary">Accept Ride</a>
                            <a href="track_order.php?id=<?php echo $booking['booking_id']; ?>&type=rider" class="btn btn-outline">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-clipboard-list"></i>
                    <h4>No available rides</h4>
                    <p>There are no pending rides available at the moment. Check back soon!</p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Recent Activity -->
        <section class="history-section">
            <h3 class="section-title"><i class="fas fa-history"></i> Recent Activity</h3>
            
            <?php if ($booking_history->num_rows > 0): ?>
                <?php while ($history = $booking_history->fetch_assoc()): ?>
                    <div class="history-card">
                        <div class="history-info">
                            <div class="history-icon">
                                <?php if ($history['service_type'] == 'ride'): ?>
                                    <i class="fas fa-motorcycle"></i>
                                <?php elseif ($history['service_type'] == 'delivery'): ?>
                                    <i class="fas fa-box"></i>
                                <?php elseif ($history['service_type'] == 'food'): ?>
                                    <i class="fas fa-utensils"></i>
                                <?php endif; ?>
                            </div>
                            <div class="history-details">
                                <h4>
                                    <?php 
                                    if ($history['service_type'] == 'ride') {
                                        echo 'Bike Ride';
                                    } elseif ($history['service_type'] == 'delivery') {
                                        echo 'Package Delivery';
                                    } elseif ($history['service_type'] == 'food') {
                                        echo 'Food Delivery';
                                    }
                                    ?>
                                </h4>
                                <p><?php echo htmlspecialchars($history['pickup_location']); ?> to <?php echo htmlspecialchars($history['dropoff_location']); ?></p>
                                <div class="history-time">
                                    <?php echo date('d M Y, h:i A', strtotime($history['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            PKR <?php echo number_format($history['fare'], 2); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="ride_history.php?type=rider" class="btn btn-outline">View All History</a>
                </div>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-history"></i>
                    <h4>No recent activity</h4>
                    <p>Your recent ride history will appear here.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Bykea. All rights reserved.</p>
    </footer>
    
    <script>
        // Status toggle functionality
        const statusToggle = document.getElementById('statusToggle');
        const statusText = document.getElementById('statusText');
        
        statusToggle.addEventListener('change', function() {
            let newStatus = '';
            
            if (this.checked) {
                newStatus = 'available';
                statusText.textContent = 'Available';
            } else {
                newStatus = 'offline';
                statusText.textContent = 'Offline';
            }
            
            // Update rider status via AJAX
            fetch('process_rider_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `rider_id=<?php echo $rider_id; ?>&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Status updated successfully');
                } else {
                    console.error('Failed to update status');
                    // Revert toggle if update failed
                    this.checked = !this.checked;
                    statusText.textContent = this.checked ? 'Available' : 'Offline';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert toggle if update failed
                this.checked = !this.checked;
                statusText.textContent = this.checked ? 'Available' : 'Offline';
            });
        });
        
        // Logout functionality
        document.querySelector('.logout').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php?type=rider';
            }
        });
    </script>
</body>
</html>
