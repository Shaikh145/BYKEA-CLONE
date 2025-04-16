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

// Get active bookings
$active_bookings_query = "SELECT * FROM bookings WHERE user_id = ? AND status IN ('pending', 'accepted', 'in_progress') ORDER BY created_at DESC";
$stmt_active = $conn->prepare($active_bookings_query);
$stmt_active->bind_param("i", $user_id);
$stmt_active->execute();
$active_bookings = $stmt_active->get_result();

// Get recent completed/cancelled bookings (limit to 5)
$history_query = "SELECT * FROM bookings WHERE user_id = ? AND status IN ('completed', 'cancelled') ORDER BY created_at DESC LIMIT 5";
$stmt_history = $conn->prepare($history_query);
$stmt_history->bind_param("i", $user_id);
$stmt_history->execute();
$booking_history = $stmt_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bykea</title>
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
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        /* Service Cards */
        .services-section {
            margin-bottom: 40px;
        }
        
        .services-section h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .service-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .service-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
        }
        
        .service-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .service-card h4 {
            margin-bottom: 10px;
        }
        
        /* Active Bookings Section */
        .bookings-section {
            margin-bottom: 40px;
        }
        
        .bookings-section h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
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
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: var(--white);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
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
        .activity-section h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .activity-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .activity-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(47, 179, 74, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
        }
        
        .activity-details h4 {
            margin-bottom: 5px;
        }
        
        .activity-time {
            font-size: 0.9rem;
            color: #666;
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
            .service-cards {
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
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                    
                    <div class="user-dropdown">
                        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
                        <a href="ride_history.php"><i class="fas fa-history"></i> Ride History</a>
                        <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
        </div>
        
        <!-- Services Section -->
        <section class="services-section">
            <h3>Our Services</h3>
            <div class="service-cards">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h4>Book a Ride</h4>
                    <p>Quick and affordable bike rides to get you to your destination.</p>
                    <a href="book_ride.php" class="btn btn-primary">Book Now</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h4>Send a Package</h4>
                    <p>Fast delivery service for your packages across the city.</p>
                    <a href="book_delivery.php" class="btn btn-primary">Send Package</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h4>Order Food</h4>
                    <p>Order from your favorite restaurants with our food delivery service.</p>
                    <a href="book_food.php" class="btn btn-primary">Order Food</a>
                </div>
            </div>
        </section>
        
        <!-- Active Bookings Section -->
        <section class="bookings-section">
            <h3>Active Bookings</h3>
            
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
                            <?php if ($booking['status'] == 'accepted' || $booking['status'] == 'in_progress'): ?>
                                <?php
                                    // Get rider details
                                    $rider_query = "SELECT full_name, phone FROM riders WHERE rider_id = ?";
                                    $stmt_rider = $conn->prepare($rider_query);
                                    $stmt_rider->bind_param("i", $booking['rider_id']);
                                    $stmt_rider->execute();
                                    $rider_result = $stmt_rider->get_result();
                                    $rider = $rider_result->fetch_assoc();
                                ?>
                                <div class="booking-detail">
                                    <div class="booking-detail-label">Rider:</div>
                                    <div><?php echo htmlspecialchars($rider['full_name']); ?> (<?php echo htmlspecialchars($rider['phone']); ?>)</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="booking-actions">
                            <a href="track_order.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-outline"><i class="fas fa-map-marker-alt"></i> Track</a>
                            <?php if ($booking['status'] == 'pending'): ?>
                                <a href="#" class="btn btn-danger" onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)"><i class="fas fa-times"></i> Cancel</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-clipboard-list"></i>
                    <h4>No active bookings</h4>
                    <p>You don't have any active bookings at the moment. Book a ride or delivery now!</p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Recent Activity -->
        <section class="activity-section">
            <h3>Recent Activity</h3>
            
            <?php if ($booking_history->num_rows > 0): ?>
                <?php while ($activity = $booking_history->fetch_assoc()): ?>
                    <div class="activity-card">
                        <div class="activity-info">
                            <div class="activity-icon">
                                <?php if ($activity['service_type'] == 'ride'): ?>
                                    <i class="fas fa-motorcycle"></i>
                                <?php elseif ($activity['service_type'] == 'delivery'): ?>
                                    <i class="fas fa-box"></i>
                                <?php elseif ($activity['service_type'] == 'food'): ?>
                                    <i class="fas fa-utensils"></i>
                                <?php endif; ?>
                            </div>
                            <div class="activity-details">
                                <h4>
                                    <?php 
                                    if ($activity['service_type'] == 'ride') {
                                        echo 'Bike Ride';
                                    } elseif ($activity['service_type'] == 'delivery') {
                                        echo 'Package Delivery';
                                    } elseif ($activity['service_type'] == 'food') {
                                        echo 'Food Delivery';
                                    }
                                    echo ' - ' . ucfirst($activity['status']);
                                    ?>
                                </h4>
                                <p><?php echo htmlspecialchars($activity['pickup_location']); ?> to <?php echo htmlspecialchars($activity['dropoff_location']); ?></p>
                                <div class="activity-time">
                                    <?php echo date('d M Y, h:i A', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            PKR <?php echo number_format($activity['fare'], 2); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="ride_history.php" class="btn btn-outline">View All History</a>
                </div>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-history"></i>
                    <h4>No recent activity</h4>
                    <p>Your recent booking history will appear here.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Bykea. All rights reserved.</p>
    </footer>
    
    <script>
        // Function to cancel booking
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                window.location.href = 'process_booking.php?action=cancel&id=' + bookingId;
            }
        }
        
        // Logout functionality
        document.querySelector('.logout').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });
    </script>
</body>
</html>
