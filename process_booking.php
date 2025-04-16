<?php
session_start();
require_once 'db.php';

// Check if user/rider is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['rider_id'])) {
    header("Location: login.php");
    exit;
}

// Check if the necessary parameters are provided
if (!isset($_GET['action']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect based on user type
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php?error=invalid_request");
    } else {
        header("Location: rider_dashboard.php?error=invalid_request");
    }
    exit;
}

$action = $_GET['action'];
$booking_id = $_GET['id'];
$user_type = isset($_SESSION['user_id']) ? 'user' : 'rider';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['rider_id'];

// Get booking information
$query = "SELECT * FROM bookings WHERE booking_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Booking not found
    if ($user_type == 'user') {
        header("Location: dashboard.php?error=booking_not_found");
    } else {
        header("Location: rider_dashboard.php?error=booking_not_found");
    }
    exit;
}

$booking = $result->fetch_assoc();

// Handle actions based on user type and booking status
switch ($action) {
    case 'cancel':
        // Only users can cancel their own bookings that are in pending status
        if ($user_type == 'user' && $booking['user_id'] == $user_id && $booking['status'] == 'pending') {
            $update_query = "UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE booking_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("i", $booking_id);
            
            if ($stmt_update->execute()) {
                header("Location: dashboard.php?success=booking_cancelled");
            } else {
                header("Location: dashboard.php?error=update_failed");
            }
        } else {
            header("Location: dashboard.php?error=unauthorized");
        }
        break;
        
    case 'accept':
        // Only riders can accept pending bookings
        if ($user_type == 'rider' && $booking['status'] == 'pending' && !$booking['rider_id']) {
            // Check if rider exists and is available
            $rider_query = "SELECT * FROM riders WHERE rider_id = ? AND status = 'available'";
            $stmt_rider = $conn->prepare($rider_query);
            $stmt_rider->bind_param("i", $user_id);
            $stmt_rider->execute();
            
            if ($stmt_rider->get_result()->num_rows === 0) {
                header("Location: rider_dashboard.php?error=rider_unavailable");
                exit;
            }
            
            // Update booking status and assign rider
            $update_query = "UPDATE bookings SET status = 'accepted', rider_id = ?, updated_at = NOW() WHERE booking_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("ii", $user_id, $booking_id);
            
            if ($stmt_update->execute()) {
                header("Location: rider_dashboard.php?success=booking_accepted");
            } else {
                header("Location: rider_dashboard.php?error=update_failed");
            }
        } else {
            header("Location: rider_dashboard.php?error=unauthorized");
        }
        break;
        
    case 'start':
        // Only assigned riders can start accepted bookings
        if ($user_type == 'rider' && $booking['rider_id'] == $user_id && $booking['status'] == 'accepted') {
            $update_query = "UPDATE bookings SET status = 'in_progress', updated_at = NOW() WHERE booking_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("i", $booking_id);
            
            if ($stmt_update->execute()) {
                header("Location: rider_dashboard.php?success=ride_started");
            } else {
                header("Location: rider_dashboard.php?error=update_failed");
            }
        } else {
            header("Location: rider_dashboard.php?error=unauthorized");
        }
        break;
        
    case 'complete':
        // Only assigned riders can complete in-progress bookings
        if ($user_type == 'rider' && $booking['rider_id'] == $user_id && $booking['status'] == 'in_progress') {
            $update_query = "UPDATE bookings SET status = 'completed', updated_at = NOW() WHERE booking_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("i", $booking_id);
            
            if ($stmt_update->execute()) {
                header("Location: rider_dashboard.php?success=ride_completed");
            } else {
                header("Location: rider_dashboard.php?error=update_failed");
            }
        } else {
            header("Location: rider_dashboard.php?error=unauthorized");
        }
        break;
        
    default:
        // Invalid action
        if ($user_type == 'user') {
            header("Location: dashboard.php?error=invalid_action");
        } else {
            header("Location: rider_dashboard.php?error=invalid_action");
        }
}

$conn->close();
?>
