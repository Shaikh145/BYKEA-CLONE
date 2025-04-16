<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bykea</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Internal CSS */
        :root {
            --primary-color: #2fb34a;
            --secondary-color: #1a8b32;
            --text-color: #333;
            --light-gray: #f5f5f5;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        
        .logo img {
            height: 40px;
        }
        
        .logo h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-left: 10px;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 20px 40px;
        }
        
        .register-container {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 500px;
            padding: 30px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #666;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .form-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .form-check-input {
            margin-top: 5px;
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
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .register-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }
        
        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .separator span {
            padding: 0 10px;
            color: #666;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                max-width: 100%;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
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
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="register-container">
            <div class="register-header">
                <h2>Create Your Account</h2>
                <p>Join Bykea and start your journey</p>
            </div>
            
            <?php
            // Display error messages from redirect
            if (isset($_GET['error'])) {
                $error = '';
                switch ($_GET['error']) {
                    case 'empty_fields':
                        $error = 'Please fill in all required fields.';
                        break;
                    case 'invalid_email':
                        $error = 'Please enter a valid email address.';
                        break;
                    case 'invalid_phone':
                        $error = 'Please enter a valid phone number (e.g., +923xxxxxxxxx or 03xxxxxxxxx).';
                        break;
                    case 'email_exists':
                        $error = 'This email is already registered. Please use a different email.';
                        break;
                    case 'phone_exists':
                        $error = 'This phone number is already registered. Please use a different number.';
                        break;
                    case 'password_short':
                        $error = 'Password must be at least 6 characters long.';
                        break;
                    case 'password_mismatch':
                        $error = 'Passwords do not match. Please try again.';
                        break;
                    case 'registration_failed':
                        $error = 'Registration failed. Please try again.';
                        break;
                    default:
                        $error = 'An unexpected error occurred. Please try again.';
                }
                echo '<div class="alert alert-danger">' . $error . '</div>';
            }
            ?>
            
            <form action="process_register.php" method="post" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Enter your full name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="e.g., 03xxxxxxxxx" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Enter your address">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="termsAgreed" name="termsAgreed" required>
                    <label class="form-check-label" for="termsAgreed">
                        I agree to Bykea's <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <div class="register-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </div>
            
            <div class="separator">
                <span>OR</span>
            </div>
            
            <div class="register-footer">
                <p>Want to be a rider? <a href="rider_register.php">Register as Rider</a></p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Bykea. All rights reserved.</p>
    </footer>

    <script>
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const termsAgreed = document.getElementById('termsAgreed').checked;
            
            let isValid = true;
            let errorMessage = '';
            
            // Check required fields
            if (!fullName || !email || !phone || !password || !confirmPassword) {
                isValid = false;
                errorMessage = 'Please fill in all required fields.';
            }
            // Check email format
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }
            // Check phone format (Pakistan)
            else if (!/^(\+92|0)[0-9]{10}$/.test(phone)) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number (e.g., +923xxxxxxxxx or 03xxxxxxxxx).';
            }
            // Check password length
            else if (password.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters long.';
            }
            // Check passwords match
            else if (password !== confirmPassword) {
                isValid = false;
                errorMessage = 'Passwords do not match. Please try again.';
            }
            // Check terms agreed
            else if (!termsAgreed) {
                isValid = false;
                errorMessage = 'You must agree to the Terms of Service and Privacy Policy.';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
    </script>
</body>
</html>
