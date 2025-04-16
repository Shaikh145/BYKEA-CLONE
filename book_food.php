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
    <title>Order Food - Bykea</title>
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
        
        .section-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        
        /* Restaurant Search and Selection */
        .restaurant-search {
            margin-bottom: 30px;
        }
        
        .search-container {
            position: relative;
        }
        
        .restaurant-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .restaurant-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .restaurant-image {
            height: 180px;
            overflow: hidden;
        }
        
        .restaurant-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .restaurant-card:hover .restaurant-image img {
            transform: scale(1.05);
        }
        
        .restaurant-info {
            padding: 15px;
        }
        
        .restaurant-name {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .restaurant-cuisine {
            color: #666;
            margin-bottom: 10px;
        }
        
        .restaurant-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .rating {
            color: #f5a623;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Menu items */
        .menu-section {
            display: none;
            margin-top: 30px;
        }
        
        .restaurant-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .restaurant-back {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: var(--primary-color);
        }
        
        .menu-categories {
            display: flex;
            gap: 15px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        
        .menu-category {
            padding: 8px 15px;
            border-radius: 20px;
            background-color: var(--light-gray);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
        }
        
        .menu-category.active {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .menu-items {
            margin-bottom: 20px;
        }
        
        .menu-item {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
        }
        
        .menu-item-details {
            flex: 1;
        }
        
        .menu-item-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .menu-item-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .menu-item-price {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .menu-item-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item-quantity {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: none;
            background-color: var(--light-gray);
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background-color: var(--border-color);
        }
        
        .quantity-input {
            width: 40px;
            height: 30px;
            border: none;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .quantity-input:focus {
            outline: none;
        }
        
        /* Cart Section */
        .cart-section {
            margin-top: 30px;
            display: none;
        }
        
        .cart-items {
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .cart-item-name {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cart-item-quantity {
            background-color: var(--primary-color);
            color: var(--white);
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .cart-item-price {
            font-weight: bold;
        }
        
        .cart-summary {
            background-color: var(--light-gray);
            padding: 15px;
            border-radius: 8px;
        }
        
        .cart-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .cart-total {
            font-weight: bold;
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
            margin-top: 10px;
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
            
            .restaurant-list {
                grid-template-columns: 1fr;
            }
            
            .payment-options {
                flex-direction: column;
                gap: 10px;
            }
            
            .menu-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .menu-item-actions {
                justify-content: flex-end;
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
            <h2>Order Food</h2>
            <p>Browse restaurants, choose your meal, and have it delivered to your doorstep</p>
        </div>
        
        <div class="booking-container">
            <!-- Phase 1: Restaurant search and selection -->
            <div id="restaurantSearchSection" class="restaurant-search">
                <div class="form-group location-container">
                    <label for="deliveryLocation">Delivery Location</label>
                    <input type="text" class="form-control" id="deliveryLocation" placeholder="Enter your delivery address" required>
                    <div class="address-suggestion" id="deliverySuggestions"></div>
                </div>
                
                <div class="form-group search-container">
                    <label for="restaurantSearch">Search Restaurants</label>
                    <input type="text" class="form-control" id="restaurantSearch" placeholder="Search by restaurant name or cuisine">
                </div>
                
                <div class="restaurant-list" id="restaurantList">
                    <!-- Restaurant cards will be added dynamically -->
                </div>
            </div>
            
            <!-- Phase 2: Menu items selection -->
            <div id="menuSection" class="menu-section">
                <div class="restaurant-header">
                    <div class="restaurant-back" id="backToRestaurants">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to restaurants</span>
                    </div>
                    <h3 id="selectedRestaurantName"></h3>
                </div>
                
                <div class="menu-categories" id="menuCategories">
                    <!-- Categories will be added dynamically -->
                </div>
                
                <div class="menu-items" id="menuItems">
                    <!-- Menu items will be added dynamically -->
                </div>
                
                <div style="text-align: center;">
                    <button id="viewCartBtn" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> 
                        View Cart (<span id="cartItemCount">0</span>)
                    </button>
                </div>
            </div>
            
            <!-- Phase 3: Cart and checkout -->
            <div id="cartSection" class="cart-section">
                <div class="section-title">Your Order</div>
                
                <div class="restaurant-header">
                    <div class="restaurant-back" id="backToMenu">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to menu</span>
                    </div>
                    <h3 id="cartRestaurantName"></h3>
                </div>
                
                <div class="cart-items" id="cartItems">
                    <!-- Cart items will be added dynamically -->
                </div>
                
                <div class="cart-summary">
                    <div class="cart-row">
                        <div>Subtotal</div>
                        <div>PKR <span id="subtotal">0.00</span></div>
                    </div>
                    <div class="cart-row">
                        <div>Delivery Fee</div>
                        <div>PKR <span id="deliveryFee">50.00</span></div>
                    </div>
                    <div class="cart-row">
                        <div>Service Fee</div>
                        <div>PKR <span id="serviceFee">20.00</span></div>
                    </div>
                    <div class="cart-row cart-total">
                        <div>Total</div>
                        <div>PKR <span id="orderTotal">70.00</span></div>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <label for="deliveryNotes">Delivery Instructions (Optional)</label>
                    <textarea class="form-control" id="deliveryNotes" rows="3" placeholder="Any special instructions for delivery?"></textarea>
                </div>
                
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
                </div>
                
                <div style="margin-top: 30px; text-align: center;">
                    <button id="placeOrderBtn" class="btn btn-primary btn-block">Place Order</button>
                </div>
            </div>
            
            <form id="orderForm" action="process_booking.php" method="post" style="display: none;">
                <input type="hidden" name="service_type" value="food">
                <input type="hidden" name="restaurantName" id="formRestaurantName" value="">
                <input type="hidden" name="restaurantAddress" id="formRestaurantAddress" value="">
                <input type="hidden" name="deliveryLocation" id="formDeliveryLocation" value="">
                <input type="hidden" name="orderItems" id="formOrderItems" value="">
                <input type="hidden" name="orderTotal" id="formOrderTotal" value="">
                <input type="hidden" name="deliveryNotes" id="formDeliveryNotes" value="">
                <input type="hidden" name="paymentMethod" id="formPaymentMethod" value="cash">
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Bykea. All rights reserved.</p>
    </footer>
    
    <script>
        // Sample addresses for delivery location suggestions
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
        
        // Sample restaurants data
        const restaurants = [
            {
                id: 1,
                name: "Karachi Biryani House",
                cuisine: "Pakistani, Biryani",
                address: "Block 4, Gulshan-e-Iqbal, Karachi",
                rating: 4.7,
                deliveryTime: "30-40",
                image: "https://images.unsplash.com/photo-1473093295043-cdd812d0e601",
                menu: {
                    categories: ["Biryanis", "Kebabs", "Curries", "Beverages"],
                    items: [
                        { id: 101, name: "Chicken Biryani", category: "Biryanis", price: 250, description: "Fragrant basmati rice cooked with tender chicken pieces and authentic spices." },
                        { id: 102, name: "Beef Biryani", category: "Biryanis", price: 300, description: "Flavorful rice dish with tender beef and aromatic spices." },
                        { id: 103, name: "Seekh Kebab", category: "Kebabs", price: 200, description: "Juicy minced meat kebabs grilled to perfection with special spices." },
                        { id: 104, name: "Chicken Tikka", category: "Kebabs", price: 180, description: "Tender chicken pieces marinated and grilled in a clay oven." },
                        { id: 105, name: "Chicken Karahi", category: "Curries", price: 350, description: "Spicy chicken dish cooked with tomatoes, green chilies and ginger." },
                        { id: 106, name: "Soft Drink", category: "Beverages", price: 60, description: "Chilled soft drinks of your choice." },
                        { id: 107, name: "Lassi", category: "Beverages", price: 100, description: "Traditional yogurt-based drink." }
                    ]
                }
            },
            {
                id: 2,
                name: "Pizza Point",
                cuisine: "Italian, Fast Food",
                address: "Tariq Road, Karachi",
                rating: 4.5,
                deliveryTime: "35-45",
                image: "https://images.unsplash.com/photo-1498837167922-ddd27525d352",
                menu: {
                    categories: ["Pizzas", "Pasta", "Appetizers", "Drinks"],
                    items: [
                        { id: 201, name: "Margherita Pizza", category: "Pizzas", price: 450, description: "Classic pizza with tomato sauce, mozzarella cheese, and fresh basil." },
                        { id: 202, name: "Pepperoni Pizza", category: "Pizzas", price: 550, description: "Pizza topped with pepperoni slices and cheese." },
                        { id: 203, name: "Chicken Alfredo Pasta", category: "Pasta", price: 400, description: "Creamy pasta with grilled chicken and parmesan cheese." },
                        { id: 204, name: "Garlic Bread", category: "Appetizers", price: 150, description: "Fresh bread with garlic butter and herbs." },
                        { id: 205, name: "Mozzarella Sticks", category: "Appetizers", price: 250, description: "Breaded mozzarella sticks served with marinara sauce." },
                        { id: 206, name: "Soft Drink", category: "Drinks", price: 80, description: "Chilled soft drinks of your choice." }
                    ]
                }
            },
            {
                id: 3,
                name: "Burger Lab",
                cuisine: "American, Fast Food",
                address: "DHA Phase 5, Karachi",
                rating: 4.6,
                deliveryTime: "25-35",
                image: "https://images.unsplash.com/photo-1464454709131-ffd692591ee5",
                menu: {
                    categories: ["Burgers", "Sides", "Desserts", "Drinks"],
                    items: [
                        { id: 301, name: "Classic Beef Burger", category: "Burgers", price: 350, description: "Juicy beef patty with lettuce, tomato, and special sauce." },
                        { id: 302, name: "Chicken Burger", category: "Burgers", price: 320, description: "Grilled chicken breast with mayo and fresh veggies." },
                        { id: 303, name: "Double Trouble Burger", category: "Burgers", price: 500, description: "Double beef patties with cheese, bacon, and signature sauce." },
                        { id: 304, name: "French Fries", category: "Sides", price: 150, description: "Crispy golden fries seasoned with salt." },
                        { id: 305, name: "Onion Rings", category: "Sides", price: 180, description: "Crispy breaded onion rings served with dipping sauce." },
                        { id: 306, name: "Chocolate Shake", category: "Drinks", price: 200, description: "Rich and creamy chocolate milkshake." }
                    ]
                }
            },
            {
                id: 4,
                name: "Spice of Asia",
                cuisine: "Chinese, Thai",
                address: "Clifton Block 8, Karachi",
                rating: 4.3,
                deliveryTime: "40-50",
                image: "https://images.unsplash.com/photo-1454944338482-a69bb95894af",
                menu: {
                    categories: ["Chinese", "Thai", "Appetizers", "Drinks"],
                    items: [
                        { id: 401, name: "Chicken Chow Mein", category: "Chinese", price: 300, description: "Stir-fried noodles with chicken and vegetables." },
                        { id: 402, name: "Kung Pao Chicken", category: "Chinese", price: 350, description: "Spicy stir-fried chicken with peanuts and vegetables." },
                        { id: 403, name: "Thai Green Curry", category: "Thai", price: 400, description: "Fragrant green curry with chicken and vegetables." },
                        { id: 404, name: "Pad Thai", category: "Thai", price: 380, description: "Thai style stir-fried noodles with bean sprouts and peanuts." },
                        { id: 405, name: "Spring Rolls", category: "Appetizers", price: 220, description: "Crispy rolls filled with vegetables and served with sweet chili sauce." },
                        { id: 406, name: "Thai Iced Tea", category: "Drinks", price: 150, description: "Sweet and creamy Thai tea served over ice." }
                    ]
                }
            }
        ];
        
        // Global variables to track state
        let selectedRestaurant = null;
        let cartItems = [];
        
        // Function to show address suggestions
        function showDeliverySuggestions() {
            const input = document.getElementById('deliveryLocation');
            const suggestionsContainer = document.getElementById('deliverySuggestions');
            
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
        
        // Function to render restaurant list
        function renderRestaurantList(restaurants) {
            const restaurantListElement = document.getElementById('restaurantList');
            restaurantListElement.innerHTML = '';
            
            restaurants.forEach(restaurant => {
                const restaurantCard = document.createElement('div');
                restaurantCard.className = 'restaurant-card';
                restaurantCard.innerHTML = `
                    <div class="restaurant-image">
                        <img src="${restaurant.image}" alt="${restaurant.name}">
                    </div>
                    <div class="restaurant-info">
                        <div class="restaurant-name">${restaurant.name}</div>
                        <div class="restaurant-cuisine">${restaurant.cuisine}</div>
                        <div class="restaurant-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i> ${restaurant.rating}
                            </div>
                            <div>${restaurant.deliveryTime} min</div>
                        </div>
                    </div>
                `;
                
                restaurantCard.addEventListener('click', function() {
                    selectRestaurant(restaurant);
                });
                
                restaurantListElement.appendChild(restaurantCard);
            });
        }
        
        // Function to handle restaurant selection
        function selectRestaurant(restaurant) {
            selectedRestaurant = restaurant;
            
            // Update restaurant name in the menu section
            document.getElementById('selectedRestaurantName').textContent = restaurant.name;
            document.getElementById('cartRestaurantName').textContent = restaurant.name;
            
            // Render menu categories
            renderMenuCategories(restaurant.menu.categories);
            
            // Render all menu items initially
            renderMenuItems(restaurant.menu.items);
            
            // Switch to menu section
            document.getElementById('restaurantSearchSection').style.display = 'none';
            document.getElementById('menuSection').style.display = 'block';
            document.getElementById('cartSection').style.display = 'none';
        }
        
        // Function to render menu categories
        function renderMenuCategories(categories) {
            const menuCategoriesElement = document.getElementById('menuCategories');
            menuCategoriesElement.innerHTML = '';
            
            // Add "All" category first
            const allCategory = document.createElement('div');
            allCategory.className = 'menu-category active';
            allCategory.textContent = 'All';
            allCategory.dataset.category = 'all';
            menuCategoriesElement.appendChild(allCategory);
            
            categories.forEach(category => {
                const categoryElement = document.createElement('div');
                categoryElement.className = 'menu-category';
                categoryElement.textContent = category;
                categoryElement.dataset.category = category;
                menuCategoriesElement.appendChild(categoryElement);
            });
            
            // Add event listeners to categories
            document.querySelectorAll('.menu-category').forEach(categoryEl => {
                categoryEl.addEventListener('click', function() {
                    document.querySelectorAll('.menu-category').forEach(el => {
                        el.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    const selectedCategory = this.dataset.category;
                    if (selectedCategory === 'all') {
                        renderMenuItems(selectedRestaurant.menu.items);
                    } else {
                        const filteredItems = selectedRestaurant.menu.items.filter(item => 
                            item.category === selectedCategory
                        );
                        renderMenuItems(filteredItems);
                    }
                });
            });
        }
        
        // Function to render menu items
        function renderMenuItems(items) {
            const menuItemsElement = document.getElementById('menuItems');
            menuItemsElement.innerHTML = '';
            
            items.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'menu-item';
                
                // Find if item is already in cart
                const cartItem = cartItems.find(cartItem => cartItem.id === item.id);
                const itemQuantity = cartItem ? cartItem.quantity : 0;
                
                itemElement.innerHTML = `
                    <div class="menu-item-details">
                        <div class="menu-item-title">${item.name}</div>
                        <div class="menu-item-description">${item.description}</div>
                        <div class="menu-item-price">PKR ${item.price.toFixed(2)}</div>
                    </div>
                    <div class="menu-item-actions">
                        <div class="item-quantity" data-item-id="${item.id}">
                            <button class="quantity-btn decrement" ${itemQuantity === 0 ? 'disabled' : ''}>-</button>
                            <input type="text" class="quantity-input" value="${itemQuantity}" readonly>
                            <button class="quantity-btn increment">+</button>
                        </div>
                    </div>
                `;
                
                menuItemsElement.appendChild(itemElement);
            });
            
            // Add event listeners to quantity buttons
            document.querySelectorAll('.quantity-btn.increment').forEach(btn => {
                btn.addEventListener('click', function() {
                    const quantityContainer = this.closest('.item-quantity');
                    const itemId = parseInt(quantityContainer.dataset.itemId);
                    const quantityInput = quantityContainer.querySelector('.quantity-input');
                    const decrementBtn = quantityContainer.querySelector('.quantity-btn.decrement');
                    
                    let quantity = parseInt(quantityInput.value) + 1;
                    quantityInput.value = quantity;
                    decrementBtn.disabled = false;
                    
                    // Update cart
                    addToCart(itemId, quantity);
                });
            });
            
            document.querySelectorAll('.quantity-btn.decrement').forEach(btn => {
                btn.addEventListener('click', function() {
                    const quantityContainer = this.closest('.item-quantity');
                    const itemId = parseInt(quantityContainer.dataset.itemId);
                    const quantityInput = quantityContainer.querySelector('.quantity-input');
                    
                    let quantity = parseInt(quantityInput.value) - 1;
                    if (quantity < 0) quantity = 0;
                    quantityInput.value = quantity;
                    
                    if (quantity === 0) {
                        this.disabled = true;
                    }
                    
                    // Update cart
                    addToCart(itemId, quantity);
                });
            });
        }
        
        // Function to add item to cart
        function addToCart(itemId, quantity) {
            // Find the item in the selected restaurant's menu
            const item = selectedRestaurant.menu.items.find(item => item.id === itemId);
            
            // Check if item is already in cart
            const existingItemIndex = cartItems.findIndex(cartItem => cartItem.id === itemId);
            
            if (existingItemIndex >= 0) {
                if (quantity === 0) {
                    // Remove item if quantity is 0
                    cartItems.splice(existingItemIndex, 1);
                } else {
                    // Update quantity if item exists
                    cartItems[existingItemIndex].quantity = quantity;
                }
            } else if (quantity > 0) {
                // Add new item to cart
                cartItems.push({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: quantity
                });
            }
            
            // Update cart count in UI
            document.getElementById('cartItemCount').textContent = cartItems.reduce((total, item) => total + item.quantity, 0);
            
            // Update cart section if it's visible
            if (document.getElementById('cartSection').style.display === 'block') {
                renderCart();
            }
        }
        
        // Function to render cart
        function renderCart() {
            const cartItemsElement = document.getElementById('cartItems');
            cartItemsElement.innerHTML = '';
            
            if (cartItems.length === 0) {
                cartItemsElement.innerHTML = '<div style="text-align: center; padding: 20px;">Your cart is empty</div>';
                return;
            }
            
            let subtotal = 0;
            
            cartItems.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item';
                itemElement.innerHTML = `
                    <div class="cart-item-name">
                        <div class="cart-item-quantity">${item.quantity}</div>
                        <div>${item.name}</div>
                    </div>
                    <div class="cart-item-price">PKR ${itemTotal.toFixed(2)}</div>
                `;
                
                cartItemsElement.appendChild(itemElement);
            });
            
            // Update summary
            const deliveryFee = 50;
            const serviceFee = 20;
            const total = subtotal + deliveryFee + serviceFee;
            
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('orderTotal').textContent = total.toFixed(2);
            
            // Update form values
            document.getElementById('formOrderTotal').value = total;
            
            // Prepare order items for form submission
            const orderItemsJson = JSON.stringify(cartItems.map(item => ({
                name: item.name,
                price: item.price,
                quantity: item.quantity
            })));
            document.getElementById('formOrderItems').value = orderItemsJson;
        }
        
        // Initialize the page
        function initPage() {
            // Show delivery address suggestions
            showDeliverySuggestions();
            
            // Render restaurant list
            renderRestaurantList(restaurants);
            
            // Handle restaurant search
            document.getElementById('restaurantSearch').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                if (searchTerm === '') {
                    renderRestaurantList(restaurants);
                } else {
                    const filteredRestaurants = restaurants.filter(restaurant => 
                        restaurant.name.toLowerCase().includes(searchTerm) || 
                        restaurant.cuisine.toLowerCase().includes(searchTerm)
                    );
                    renderRestaurantList(filteredRestaurants);
                }
            });
            
            // Handle payment method selection
            document.querySelectorAll('.payment-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.payment-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    this.classList.add('selected');
                    document.getElementById('formPaymentMethod').value = this.getAttribute('data-payment');
                });
            });
            
            // Handle navigation between sections
            document.getElementById('backToRestaurants').addEventListener('click', function() {
                document.getElementById('restaurantSearchSection').style.display = 'block';
                document.getElementById('menuSection').style.display = 'none';
                document.getElementById('cartSection').style.display = 'none';
            });
            
            document.getElementById('viewCartBtn').addEventListener('click', function() {
                if (cartItems.length === 0) {
                    alert('Your cart is empty. Please add items to proceed.');
                    return;
                }
                
                document.getElementById('restaurantSearchSection').style.display = 'none';
                document.getElementById('menuSection').style.display = 'none';
                document.getElementById('cartSection').style.display = 'block';
                
                renderCart();
            });
            
            document.getElementById('backToMenu').addEventListener('click', function() {
                document.getElementById('restaurantSearchSection').style.display = 'none';
                document.getElementById('menuSection').style.display = 'block';
                document.getElementById('cartSection').style.display = 'none';
            });
            
            // Handle order placement
            document.getElementById('placeOrderBtn').addEventListener('click', function() {
                if (cartItems.length === 0) {
                    alert('Your cart is empty. Please add items before placing order.');
                    return;
                }
                
                const deliveryLocation = document.getElementById('deliveryLocation').value.trim();
                if (!deliveryLocation) {
                    alert('Please enter your delivery address');
                    return;
                }
                
                // Prepare form data for submission
                document.getElementById('formRestaurantName').value = selectedRestaurant.name;
                document.getElementById('formRestaurantAddress').value = selectedRestaurant.address;
                document.getElementById('formDeliveryLocation').value = deliveryLocation;
                document.getElementById('formDeliveryNotes').value = document.getElementById('deliveryNotes').value;
                
                // Submit the form
                document.getElementById('orderForm').submit();
            });
        }
        
        // Call init function when page loads
        window.addEventListener('load', initPage);
    </script>
</body>
</html>
