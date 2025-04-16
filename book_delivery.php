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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Delivery - Bykea</title>
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
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group.flex-1 {
            flex: 1;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            border: none;
            font-size: 1rem;
        }
        
        .btn-block {
            display: block;
            width: 100%;
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
        
        .address-suggestion {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-top: none;
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
        }
        
        .address-option {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .address-option:hover {
            background-color: var(--light-gray);
        }
        
        .price-estimate {
            background-color: rgba(47, 179, 74, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .price-total {
            font-weight: bold;
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .location-container {
            position: relative;
        }
        
        .payment-options {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        
        .payment-option {
            flex: 1;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option.selected {
            border-color: var(--primary-color);
            background-color: rgba(47, 179, 74, 0.1);
        }
        
        .payment-option i {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .package-types {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .package-type {
            flex: 1;
            min-width: 150px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .package-type.selected {
            border-color: var(--primary-color);
            background-color: rgba(47, 179, 74, 0.1);
        }
        
        .package-type i {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        /* Map Container */
        .map-container {
            height: 300px;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .map-placeholder {
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
            .booking-container {
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .payment-options,
            .package-types {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="nav-container">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
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
        <div class="booking-header">
            <h2>Send a Package</h2>
            <p>Enter package details and delivery locations</p>
        </div>
        
        <div class="booking-container">
            <form id="bookingForm" action="process_booking.php" method="post">
                <input type="hidden" name="service_type" value="delivery">
                
                <div class="map-container">
                    <div class="map-placeholder">
                        <i class="fas fa-map-marker-alt"></i> Interactive map will be displayed here
                    </div>
                </div>
                
                <div class="section-title">Delivery Details</div>
                
                <div class="form-group">
                    <label>Package Type</label>
                    <div class="package-types">
                        <div class="package-type selected" data-package="document">
                            <i class="fas fa-file-alt"></i>
                            <div>Document</div>
                            <small>Up to 1kg</small>
                        </div>
                        <div class="package-type" data-package="small_package">
                            <i class="fas fa-box"></i>
                            <div>Small Package</div>
                            <small>1-3kg</small>
                        </div>
                        <div class="package-type" data-package="medium_package">
                            <i class="fas fa-box-open"></i>
                            <div>Medium Package</div>
                            <small>3-5kg</small>
                        </div>
                        <div class="package-type" data-package="large_package">
                            <i class="fas fa-boxes"></i>
                            <div>Large Package</div>
                            <small>5-10kg</small>
                        </div>
                    </div>
                    <input type="hidden" name="packageType" id="packageType" value="document">
                </div>
                
                <div class="form-group">
                    <label for="packageWeight">Package Weight (kg)</label>
                    <input type="number" class="form-control" id="packageWeight" name="packageWeight" placeholder="Enter package weight" min="0.1" max="10" step="0.1" value="1">
                </div>
                
                <div class="section-title">Locations</div>
                
                <div class="form-group location-container">
                    <label for="pickupLocation">Pickup Location</label>
                    <input type="text" class="form-control" id="pickupLocation" name="pickupLocation" placeholder="Enter pickup address" required>
                    <div class="address-suggestion" id="pickupSuggestions"></div>
                </div>
                
                <div class="form-group location-container">
                    <label for="dropoffLocation">Drop-off Location</label>
                    <input type="text" class="form-control" id="dropoffLocation" name="dropoffLocation" placeholder="Enter destination address" required>
                    <div class="address-suggestion" id="dropoffSuggestions"></div>
                </div>
                
                <div class="section-title">Recipient Information</div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="recipientName">Recipient Name</label>
                        <input type="text" class="form-control" id="recipientName" name="recipientName" placeholder="Enter recipient's full name" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="recipientPhone">Recipient Phone</label>
                        <input type="tel" class="form-control" id="recipientPhone" name="recipientPhone" placeholder="e.g., 03xxxxxxxxx" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="specialInstructions">Special Instructions (Optional)</label>
                    <textarea class="form-control" id="specialInstructions" name="specialInstructions" rows="3" placeholder="Any special handling or delivery instructions?"></textarea>
                </div>
                
                <div class="section-title">Payment</div>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-options">
                        <div class="payment-option selected" data-payment="cash">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>Cash on Delivery</div>
                        </div>
                        <div class="payment-option" data-payment="card">
                            <i class="fas fa-credit-card"></i>
                            <div>Card</div>
                        </div>
                        <div class="payment-option" data-payment="wallet">
                            <i class="fas fa-wallet"></i>
                            <div>Wallet</div>
                        </div>
                    </div>
                    <input type="hidden" name="paymentMethod" id="paymentMethod" value="cash">
                </div>
                
                <div class="price-estimate" id="priceEstimate">
                    <h3>Delivery Estimate</h3>
                    <div class="price-row">
                        <div>Base Fare</div>
                        <div>PKR 50.00</div>
                    </div>
                    <div class="price-row">
                        <div>Distance (<span id="estimatedDistance">0</span> km)</div>
                        <div>PKR <span id="distanceFare">0.00</span></div>
                    </div>
                    <div class="price-row">
                        <div>Package Type Fee</div>
                        <div>PKR <span id="packageFee">0.00</span></div>
                    </div>
                    <div class="price-row price-total">
                        <div>Total Fare</div>
                        <div>PKR <span id="totalFare">50.00</span></div>
                    </div>
                    <input type="hidden" name="estimatedFare" id="estimatedFare" value="50">
                    <input type="hidden" name="estimatedDistance" id="distanceInput" value="0">
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <button type="button" id="calculateFare" class="btn btn-outline">Calculate Fare</button>
                    <button type="submit" class="btn btn-primary">Book Delivery</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Bykea. All rights reserved.</p>
    </footer>
    
    <script>
        // Sample addresses for demonstration
        const sampleAddresses = [
            'Clifton, Karachi',
            'Defence Housing Authority, Karachi',
            'Gulshan-e-Iqbal, Karachi',
            'Saddar, Karachi',
            'North Nazimabad, Karachi',
            'FB Area, Karachi',
            'Tariq Road, Karachi',
            'Bahadurabad, Karachi',
            'Gulistan-e-Jauhar, Karachi',
            'SITE Industrial Area, Karachi'
        ];
        
        // Function to show address suggestions
        function showSuggestions(inputId, suggestionsId) {
            const input = document.getElementById(inputId);
            const suggestionsContainer = document.getElementById(suggestionsId);
            
            input.addEventListener('focus', function() {
                const value = this.value.toLowerCase();
                suggestionsContainer.innerHTML = '';
                
                if (value.length === 0) {
                    // Show all sample addresses if input is empty
                    sampleAddresses.forEach(address => {
                        const div = document.createElement('div');
                        div.className = 'address-option';
                        div.textContent = address;
                        div.addEventListener('click', function() {
                            input.value = address;
                            suggestionsContainer.style.display = 'none';
                        });
                        suggestionsContainer.appendChild(div);
                    });
                } else {
                    // Filter addresses based on input
                    const filteredAddresses = sampleAddresses.filter(address => 
                        address.toLowerCase().includes(value)
                    );
                    
                    filteredAddresses.forEach(address => {
                        const div = document.createElement('div');
                        div.className = 'address-option';
                        div.textContent = address;
                        div.addEventListener('click', function() {
                            input.value = address;
                            suggestionsContainer.style.display = 'none';
                        });
                        suggestionsContainer.appendChild(div);
                    });
                }
                
                if (suggestionsContainer.children.length > 0) {
                    suggestionsContainer.style.display = 'block';
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            });
            
            input.addEventListener('input', function() {
                const value = this.value.toLowerCase();
                suggestionsContainer.innerHTML = '';
                
                if (value.length === 0) {
                    suggestionsContainer.style.display = 'none';
                    return;
                }
                
                const filteredAddresses = sampleAddresses.filter(address => 
                    address.toLowerCase().includes(value)
                );
                
                filteredAddresses.forEach(address => {
                    const div = document.createElement('div');
                    div.className = 'address-option';
                    div.textContent = address;
                    div.addEventListener('click', function() {
                        input.value = address;
                        suggestionsContainer.style.display = 'none';
                    });
                    suggestionsContainer.appendChild(div);
                });
                
                if (suggestionsContainer.children.length > 0) {
                    suggestionsContainer.style.display = 'block';
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== input && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.style.display = 'none';
                }
            });
        }
        
        // Initialize suggestions for both inputs
        showSuggestions('pickupLocation', 'pickupSuggestions');
        showSuggestions('dropoffLocation', 'dropoffSuggestions');
        
        // Handle payment method selection
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                document.getElementById('paymentMethod').value = this.getAttribute('data-payment');
            });
        });
        
        // Handle package type selection
        document.querySelectorAll('.package-type').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.package-type').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                document.getElementById('packageType').value = this.getAttribute('data-package');
                
                // Update weight according to package type
                const packageType = this.getAttribute('data-package');
                const weightInput = document.getElementById('packageWeight');
                
                switch(packageType) {
                    case 'document':
                        weightInput.value = 0.5;
                        break;
                    case 'small_package':
                        weightInput.value = 2;
                        break;
                    case 'medium_package':
                        weightInput.value = 4;
                        break;
                    case 'large_package':
                        weightInput.value = 7;
                        break;
                }
            });
        });
        
        // Calculate fare button
        document.getElementById('calculateFare').addEventListener('click', function() {
            const pickup = document.getElementById('pickupLocation').value;
            const dropoff = document.getElementById('dropoffLocation').value;
            const packageType = document.getElementById('packageType').value;
            const packageWeight = parseFloat(document.getElementById('packageWeight').value);
            
            if (!pickup || !dropoff) {
                alert('Please enter both pickup and drop-off locations');
                return;
            }
            
            if (isNaN(packageWeight) || packageWeight <= 0 || packageWeight > 10) {
                alert('Please enter a valid package weight between 0.1 and 10 kg');
                return;
            }
            
            // In a real app, you would call the Google Maps Distance Matrix API here
            // For this example, we'll simulate the calculation
            calculateDeliveryEstimate(pickup, dropoff, packageType, packageWeight);
        });
        
        // Function to calculate delivery estimate (simplified for example)
        function calculateDeliveryEstimate(pickup, dropoff, packageType, packageWeight) {
            // Simulate distance calculation
            // In a real app, this would be calculated using Google Maps API
            const distance = Math.floor(Math.random() * 10) + 3; // Random distance between 3-12 km
            const baseFare = 50;
            const perKmRate = 20; // Higher rate for delivery
            const distanceFare = distance * perKmRate;
            
            // Package fee based on type
            let packageFee = 0;
            switch(packageType) {
                case 'document':
                    packageFee = 10;
                    break;
                case 'small_package':
                    packageFee = 30;
                    break;
                case 'medium_package':
                    packageFee = 50;
                    break;
                case 'large_package':
                    packageFee = 80;
                    break;
            }
            
            // Add extra fee for weight over the standard for package type
            let extraWeightFee = 0;
            if ((packageType === 'document' && packageWeight > 1) ||
                (packageType === 'small_package' && packageWeight > 3) ||
                (packageType === 'medium_package' && packageWeight > 5)) {
                extraWeightFee = Math.ceil(packageWeight) * 10;
                packageFee += extraWeightFee;
            }
            
            const totalFare = baseFare + distanceFare + packageFee;
            
            // Update the UI
            document.getElementById('estimatedDistance').textContent = distance;
            document.getElementById('distanceFare').textContent = distanceFare.toFixed(2);
            document.getElementById('packageFee').textContent = packageFee.toFixed(2);
            document.getElementById('totalFare').textContent = totalFare.toFixed(2);
            
            // Update hidden inputs
            document.getElementById('estimatedFare').value = totalFare;
            document.getElementById('distanceInput').value = distance;
            
            // Show the price estimate section
            document.getElementById('priceEstimate').style.display = 'block';
        }
        
        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const pickup = document.getElementById('pickupLocation').value.trim();
            const dropoff = document.getElementById('dropoffLocation').value.trim();
            const recipientName = document.getElementById('recipientName').value.trim();
            const recipientPhone = document.getElementById('recipientPhone').value.trim();
            const packageWeight = parseFloat(document.getElementById('packageWeight').value);
            
            let isValid = true;
            let errorMessage = '';
            
            if (!pickup || !dropoff) {
                isValid = false;
                errorMessage = 'Please enter both pickup and drop-off locations';
            } else if (!recipientName) {
                isValid = false;
                errorMessage = 'Please enter recipient name';
            } else if (!recipientPhone) {
                isValid = false;
                errorMessage = 'Please enter recipient phone number';
            } else if (!/^(\+92|0)[0-9]{10}$/.test(recipientPhone)) {
                isValid = false;
                errorMessage = 'Please enter a valid Pakistan phone number';
            } else if (isNaN(packageWeight) || packageWeight <= 0 || packageWeight > 10) {
                isValid = false;
                errorMessage = 'Please enter a valid package weight between 0.1 and 10 kg';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
    </script>
</body>
</html>
