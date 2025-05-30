<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$db   = 'eventhub';
$user = 'root';  // adjust as needed
$pass = '';      // adjust as needed

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Get initials for avatar
$initials = '';
if (isset($user['email'])) {
    $parts = explode('@', $user['email']);
    $nameParts = explode('.', $parts[0]);
    foreach ($nameParts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    $initials = substr($initials, 0, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub | Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-dark: #1e3a8a;
            --primary: #3b82f6;
            --primary-light: #93c5fd;
            --accent: #facc15;
            --accent-dark: #eab308;
            --white: #ffffff;
            --gray-light: #f3f4f6;
            --gray: #9ca3af;
            --dark: #1f2937;
        }

        body {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 16px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 0 20px;
            margin: 0 auto;
        }

        .navbar {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(30, 64, 175, 0.9);
            padding: 15px 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-left img {
            height: 40px;
            margin-right: 10px;
        }

        .navbar-left .brand-name {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--accent);
        }

        .navbar-center {
            display: flex;
            gap: 5px;
        }

        .navbar-center a {
            color: var(--white);
            text-decoration: none;
            margin: 0 15px;
            font-size: 16px;
            text-transform: uppercase;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .navbar-center a:hover {
            color: var(--accent);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .navbar-center a.active {
            color: var(--dark);
            background-color: var(--accent);
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .create-btn {
            background-color: var(--accent);
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .create-btn:hover {
            background-color: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .user-profile:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--dark);
        }

        .user-name {
            font-weight: 500;
        }

        /* Dropdown styles */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: rgba(30, 64, 175, 0.95);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            padding: 10px 0;
            min-width: 180px;
            z-index: 1001;
            display: none;
            backdrop-filter: blur(5px);
        }

        .dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease-in-out;
        }

        .dropdown-menu a {
            color: var(--white);
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: all 0.3s ease;
        }

        .dropdown-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--accent);
        }

        .dropdown-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .main-content {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            width: 100%;
        }

        .welcome-section {
            margin-bottom: 40px;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .main-content h1 {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .main-content h2 {
            font-size: 1.5rem;
            margin: 10px 0 30px;
            font-weight: 400;
            opacity: 0.9;
        }

        .search-container {
            position: relative;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .search-bar {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            color: var(--dark);
        }

        .search-bar:focus {
            outline: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .category-title {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--white);
            position: relative;
            display: inline-block;
        }

        .category-title::after {
            content: '';
            position: absolute;
            left: 30%;
            bottom: -8px;
            width: 40%;
            height: 3px;
            background-color: var(--accent);
            border-radius: 2px;
        }

        .categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 30px 0;
            width: 100%;
        }

        .category {
            background-color: rgba(30, 64, 175, 0.6);
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            height: 200px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .category:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .category-img-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .category-img-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.1) 100%);
        }

        .category img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s ease;
        }

        .category:hover img {
            transform: scale(1.05);
        }

        .category-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            z-index: 1;
            text-align: left;
            transition: all 0.3s ease;
        }

        .category:hover .category-info {
            bottom: 5px;
        }

        .category-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .category-desc {
            font-size: 0.9rem;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .category:hover .category-desc {
            opacity: 1;
            max-height: 100px;
            margin-top: 8px;
        }

        .category-count {
            display: inline-block;
            background-color: var(--accent);
            color: var(--dark);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-top: 10px;
        }

        .trending-section {
            background-color: rgba(30, 64, 175, 0.6);
            padding: 30px;
            border-radius: 15px;
            margin: 40px 0;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            width: 100%;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .view-all {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .event-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .event-card {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            color: var(--dark);
        }

        .event-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .event-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .event-info {
            padding: 15px;
        }

        .event-date {
            font-size: 0.8rem;
            color: var(--gray);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .event-location {
            font-size: 0.9rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }

        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .event-price {
            background-color: var(--gray-light);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .event-interest {
            font-size: 0.8rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .footer {
            width: 100%;
            background-color: rgba(30, 64, 175, 0.9);
            margin-top: 60px;
            padding: 40px 20px;
            backdrop-filter: blur(5px);
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            gap: 30px;
        }

        .footer-column {
            flex: 1;
            min-width: 250px;
        }

        .footer-title {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--accent);
            position: relative;
            display: inline-block;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 40px;
            height: 3px;
            background-color: var(--accent);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
            opacity: 0.8;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-links a:hover {
            opacity: 1;
            color: var(--accent);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background-color: var(--accent);
            color: var(--dark);
            transform: translateY(-3px);
        }

        .newsletter-form {
            display: flex;
            margin-top: 15px;
        }

        .newsletter-input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 20px 0 0 20px;
            font-size: 14px;
        }

        .newsletter-btn {
            background-color: var(--accent);
            border: none;
            padding: 10px 15px;
            border-radius: 0 20px 20px 0;
            color: var(--dark);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .newsletter-btn:hover {
            background-color: var(--accent-dark);
        }

        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: var(--gray-light);
            opacity: 0.7;
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .navbar-center {
                display: none;
                position: absolute;
                top: 70px;
                left: 0;
                width: 100%;
                background-color: rgba(30, 64, 175, 0.95);
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(5px);
            }

            .navbar-center.show {
                display: flex;
            }

            .navbar-center a {
                margin: 10px 0;
                width: 100%;
                text-align: center;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .categories {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .navbar-right .user-name {
                display: none;
            }

            .dropdown-menu {
                right: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <img src="/api/placeholder/40/40" alt="Logo">
            <span class="brand-name">EventHub</span>
        </div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="navbar-center" id="navMenu">
            <a href="message.php" class="active">News</a>
            <a href="faq.html">FAQ</a>
            <a href="aide.html">Help</a>
            <a href="apropos.html">About Us</a>
        </div>
        <div class="navbar-right">
            <button class="create-btn">
                <i class="fas fa-plus"></i>
                <a href="create.html">create</a>
            </button>
            <div class="user-profile" id="userProfile">
                <div class="user-avatar"><?= $initials ?></div>
                <span class="user-name"><?= htmlspecialchars(explode('@', $user['email'])[0]) ?></span>
                <i class="fas fa-chevron-down"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    <a href="settings.html"><i class="fas fa-cog"></i> Settings</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="main-content">
            <div class="welcome-section">
                <h1>Welcome <?= htmlspecialchars(explode('@', $user['email'])[0]) ?></h1>
                <h2>Discover events that excite you</h2>
            </div>

            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-bar" placeholder="Search for events, locations or categories...">
            </div>

            <h3 class="category-title">Explore by Category</h3>
            <div class="categories">
                <div class="category">
                    <div class="category-img-container">
                        <img src="https://www.visit.alsace/wp-content/uploads/2018/11/festival-decibulles-2017-laurent-khram-longvixay-1-1600x900.jpg" alt="Festivals">
                    </div>
                    <div class="category-info">
                        <a href="festivals.html" class="category-link">
                            <div class="category-name">Festivals</div>
                        </a>
                        <div class="category-desc">Discover the best music, art and culture festivals.</div>
                        <span class="category-count">125 events</span>
                    </div>
                </div>
                <div class="category">
                    <div class="category-img-container">
                        <img src="https://offloadmedia.feverup.com/parissecret.com/wp-content/uploads/2023/09/06110716/Montage-photos-articles-PS-2023-12-06T110657.772-1024x683.jpg" alt="Concerts">
                    </div>
                    <div class="category-info">
                        <a href="concerts.html" class="category-link">
                            <div class="category-name">Concerts</div>
                        </a>
                        <div class="category-desc">Live performances from your favorite artists near you.</div>
                        <span class="category-count">87 events</span>
                    </div>
                </div>
                <div class="category">
                    <div class="category-img-container">
                        <img src="https://www.kantar.com/-/media/project/kantar/global/articles/images/2024/brands-sports-ads_1500x1000jpeg.jpeg" alt="Sports">
                    </div>
                    <div class="category-info">
                        <div class="category-name">Sports</div>
                        <div class="category-desc">Matches, tournaments and sports events for all fans.</div>
                        <span class="category-count">94 events</span>
                    </div>
                </div>
                <div class="category">
                    <div class="category-img-container">
                        <img src="https://i5.walmartimages.com/dfw/4ff9c6c9-e398/k2-_8d0ff3c6-d007-4f05-89f8-3e22cf81825b.v1.jpg?odnHeight=680&odnWidth=1208&odnBg=FFFFFF" alt="Games">
                    </div>
                    <div class="category-info">
                        <div class="category-name">Games</div>
                        <div class="category-desc">Video game tournaments, board game nights and more.</div>
                        <span class="category-count">56 events</span>
                    </div>
                </div>
            </div>

            <div class="trending-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-fire" style="color: var(--accent);"></i>
                        Trending Near You
                    </h3>
                    <a href="#" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="event-cards">
                    <div class="event-card">
                        <img src="/api/placeholder/280/150" alt="Jazz Festival" class="event-img">
                        <div class="event-info">
                            <div class="event-date">
                                <i class="far fa-calendar"></i>
                                Apr 15-17, 2025
                            </div>
                            <div class="event-title">International Jazz Festival</div>
                            <div class="event-location">
                                <i class="fas fa-map-marker-alt"></i>
                                Parc de la Villette, Paris
                            </div>
                            <div class="event-footer">
                                <span class="event-price">From $45</span>
                                <span class="event-interest">
                                    <i class="far fa-heart"></i>
                                    1.2k interested
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="event-card">
                        <img src="/api/placeholder/280/150" alt="Rock Concert" class="event-img">
                        <div class="event-info">
                            <div class="event-date">
                                <i class="far fa-calendar"></i>
                                Apr 22, 2025
                            </div>
                            <div class="event-title">Imagine Dragons - Live Tour</div>
                            <div class="event-location">
                                <i class="fas fa-map-marker-alt"></i>
                                AccorHotels Arena, Paris
                            </div>
                            <div class="event-footer">
                                <span class="event-price">From $75</span>
                                <span class="event-interest">
                                    <i class="far fa-heart"></i>
                                    3.5k interested
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="event-card">
                        <img src="/api/placeholder/280/150" alt="Gaming Tournament" class="event-img">
                        <div class="event-info">
                            <div class="event-date">
                                <i class="far fa-calendar"></i>
                                May 8-9, 2025
                            </div>
                            <div class="event-title">Paris E-Sport Games Tournament</div>
                            <div class="event-location">
                                <i class="fas fa-map-marker-alt"></i>
                                Paris Expo Porte de Versailles
                            </div>
                            <div class="event-footer">
                                <span class="event-price">From $25</span>
                                <span class="event-interest">
                                    <i class="far fa-heart"></i>
                                    940 interested
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h4 class="footer-title">About</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-info-circle"></i> Our Story</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Our Team</a></li>
                    <li><a href="#"><i class="fas fa-briefcase"></i> Careers</a></li>
                    <li><a href="#"><i class="fas fa-newspaper"></i> News</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4 class="footer-title">Support</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-question-circle"></i> Help Center</a></li>
                    <li><a href="#"><i class="fas fa-ticket-alt"></i> Contact Support</a></li>
                    <li><a href="#"><i class="fas fa-book"></i> Guides</a></li>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Safety</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4 class="footer-title">Legal</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-lock"></i> Privacy</a></li>
                    <li><a href="#"><i class="fas fa-file-contract"></i> Terms of Use</a></li>
                    <li><a href="#"><i class="fas fa-cookie"></i> Cookies</a></li>
                </ul>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h4 class="footer-title">Newsletter</h4>
                <p>Get the latest news and special offers directly to your inbox.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email" class="newsletter-input" required>
                    <button type="submit" class="newsletter-btn"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        <div class="copyright">
            &copy; 2025 EventHub. All rights reserved.
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navMenu = document.getElementById('navMenu');
        
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });

        // Dropdown menu functionality
        const userProfile = document.getElementById('userProfile');
        const dropdownMenu = document.getElementById('dropdownMenu');

        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking elsewhere
        document.addEventListener('click', function() {
            dropdownMenu.classList.remove('show');
        });

        // Prevent dropdown from closing when clicking inside it
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // For demonstration purposes - categories hover effects
        document.querySelectorAll('.category').forEach(category => {
            category.addEventListener('mouseenter', function() {
                this.querySelector('.category-desc').style.opacity = '1';
            });
            
            category.addEventListener('mouseleave', function() {
                this.querySelector('.category-desc').style.opacity = '0';
            });
        });
    </script>
</body>
</html>