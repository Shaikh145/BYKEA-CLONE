<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bykea - Rides, Deliveries, and More</title>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
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
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-outline {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: 2px solid var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        /* Hero Section */
        .hero {
            padding-top: 100px;
            padding-bottom: 50px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7)), url('https://images.unsplash.com/photo-1585282263861-f55e341878f8') no-repeat center center;
            background-size: cover;
            min-height: 500px;
            display: flex;
            align-items: center;
        }
        
        .hero-content {
            max-width: 600px;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .hero p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        /* Services Section */
        .services {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary-color);
            font-size: 2rem;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
        }
        
        .service-img {
            height: 200px;
            overflow: hidden;
        }
        
        .service-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .service-card:hover .service-img img {
            transform: scale(1.05);
        }
        
        .service-content {
            padding: 20px;
        }
        
        .service-content h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .service-content p {
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        /* How It Works */
        .how-it-works {
            background-color: var(--white);
            padding: 80px 0;
        }
        
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            counter-reset: step-counter;
        }
        
        .step-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            position: relative;
        }
        
        .step-card::before {
            counter-increment: step-counter;
            content: counter(step-counter);
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .step-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .step-card h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        /* Testimonials */
        .testimonials {
            padding: 80px 0;
            background-color: var(--light-gray);
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .testimonial-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 25px;
            box-shadow: var(--shadow);
        }
        
        .testimonial-content {
            margin-bottom: 20px;
            position: relative;
        }
        
        .testimonial-content::before {
            content: '"';
            font-size: 4rem;
            color: rgba(47, 179, 74, 0.2);
            position: absolute;
            top: -20px;
            left: -10px;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .author-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .author-info h4 {
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        
        .rating {
            color: #ffc107;
            margin-top: 5px;
        }
        
        /* Download App */
        .download-app {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 80px 0;
            text-align: center;
        }
        
        .download-app h2 {
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .download-app p {
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        .app-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .app-link {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: var(--white);
            color: var(--text-color);
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .app-link:hover {
            transform: translateY(-3px);
        }
        
        .app-link i {
            font-size: 1.5rem;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: var(--white);
            padding: 50px 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s;
            display: block;
        }
        
        .footer-column ul li a:hover {
            color: var(--primary-color);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero-content {
                max-width: 100%;
            }
            
            .services-grid,
            .testimonial-grid {
                grid-template-columns: 1fr;
            }
            
            .steps {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container nav-container">
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
            
            <div class="nav-links">
                <a href="#services">Services</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#testimonials">Testimonials</a>
                <a href="#download">Download App</a>
            </div>
            
            <div class="auth-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <a href="rider_login.php" class="btn btn-outline">Rider Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Move, Deliver, Order with Bykea</h2>
                <p>Pakistan's #1 app for bike rides, parcel delivery, and online food ordering. Fast, affordable, and reliable service available 24/7.</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <div class="services-grid">
                <!-- Ride Service -->
                <div class="service-card">
                    <div class="service-img">
                        <img src="https://images.unsplash.com/photo-1558981403-c5f9899a28bc" alt="Bike Ride Service">
                    </div>
                    <div class="service-content">
                        <h3>Bike Rides</h3>
                        <p>Quick, affordable bike rides to get you to your destination efficiently and safely.</p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'book_ride.php' : 'login.php'; ?>" class="btn btn-primary">Book a Ride</a>
                    </div>
                </div>
                
                <!-- Delivery Service -->
                <div class="service-card">
                    <div class="service-img">
                        <img src="https://images.unsplash.com/photo-1644674363808-7dd3c5702839" alt="Package Delivery Service">
                    </div>
                    <div class="service-content">
                        <h3>Parcel Delivery</h3>
                        <p>Send packages across the city quickly with our reliable delivery service.</p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'book_delivery.php' : 'login.php'; ?>" class="btn btn-primary">Send a Package</a>
                    </div>
                </div>
                
                <!-- Food Delivery -->
                <div class="service-card">
                    <div class="service-img">
                        <img src="https://images.unsplash.com/photo-1473093295043-cdd812d0e601" alt="Food Delivery Service">
                    </div>
                    <div class="service-content">
                        <h3>Food Delivery</h3>
                        <p>Order food from your favorite restaurants and have it delivered to your doorstep.</p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'book_food.php' : 'login.php'; ?>" class="btn btn-primary">Order Food</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps">
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Create Account</h3>
                    <p>Sign up for a free Bykea account in just a few steps.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Set Locations</h3>
                    <p>Enter your pickup and drop-off locations for your ride or delivery.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h3>Get Matched</h3>
                    <p>We'll match you with the nearest available rider in minutes.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h3>Enjoy Service</h3>
                    <p>Track your ride or delivery in real-time until completion.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <h2 class="section-title">Customer Stories</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>Bykea has been a lifesaver for my daily commute to work. Affordable, quick, and professional service every time.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-info">
                            <h4>Ahmed K.</h4>
                            <p>Regular Customer</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>I use Bykea delivery for my small business. It's reliable, cost-effective, and my customers love the quick delivery times.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-info">
                            <h4>Fatima S.</h4>
                            <p>Business Owner</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>The food delivery service is fantastic! Hot food delivered quickly, and I can track the order status in real-time.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-info">
                            <h4>Zain M.</h4>
                            <p>Food Lover</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Download App Section -->
    <section id="download" class="download-app">
        <div class="container">
            <h2>Download the Bykea App</h2>
            <p>Get the full Bykea experience by downloading our mobile app. Available for both Android and iOS.</p>
            <div class="app-links">
                <a href="#" class="app-link">
                    <i class="fab fa-google-play"></i>
                    <div>
                        <small>Get it on</small>
                        <div>Google Play</div>
                    </div>
                </a>
                <a href="#" class="app-link">
                    <i class="fab fa-apple"></i>
                    <div>
                        <small>Download on the</small>
                        <div>App Store</div>
                    </div>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Bykea</h3>
                    <p>Pakistan's leading mobility, e-commerce and payments platform providing affordable rides, deliveries, and online payments.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="#">Bike Rides</a></li>
                        <li><a href="#">Parcel Delivery</a></li>
                        <li><a href="#">Food Delivery</a></li>
                        <li><a href="#">Bykea for Business</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Safety Center</a></li>
                        <li><a href="#">Community Guidelines</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Accessibility</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 Bykea. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
