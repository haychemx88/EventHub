<?php
/*
-- SQL Setup (run once if you haven't already) ---------------------------
CREATE DATABASE eventhub;

USE eventhub;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
-------------------------------------------------------------------------
*/

session_start();

// Database connection
$host = 'localhost';
$db   = 'eventhub';
$user = 'root';  // adjust as needed
$pass = '';      // adjust as needed

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle signup POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email           = trim($_POST["email"]);
    $password        = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    if ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $message = "Email is already registered.";
        } else {
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $insert->bind_param("ss", $email, $hash);
            
            if ($insert->execute()) {
                // Redirect to login page after successful signup
                header("Location: login.php");
                exit();
            } else {
                $message = "Signup failed. Please try again.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | EventHub</title>
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

        .navbar {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(30, 64, 175, 0.9);
            padding: 15px 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: fixed;
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
            gap: 15px;
        }

        .navbar-center a {
            color: var(--white);
            text-decoration: none;
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

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Sign Up Container */
        .signup-container {
            background-color: rgba(15, 89, 185, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
            margin-top: 100px;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .signup-container h1 {
            margin-bottom: 30px;
            font-size: 2em;
            color: var(--accent);
        }

        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .signup-form div {
            text-align: left;
        }

        .signup-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .signup-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .signup-form input:focus {
            border-color: var(--accent);
            outline: none;
        }

        .signup-form button {
            background-color: var(--accent);
            border: none;
            padding: 15px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px;
            font-size: 1em;
            transition: all 0.3s;
            color: var(--dark);
            margin-top: 10px;
            width: 100%;
        }

        .signup-form button:hover {
            background-color: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .additional-links {
            margin-top: 20px;
        }

        .additional-links a {
            color: var(--accent);
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        .additional-links a:hover {
            color: var(--accent-dark);
            text-decoration: underline;
        }

        .error-message {
            color: #ff6347;
            margin-top: 10px;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            
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

            .navbar-right .user-name {
                display: none;
            }

            .signup-container {
                margin: 120px 20px 40px;
                padding: 30px 20px;
                max-width: 100%;
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
        <div class="navbar-center" id="navMenu">
            <a href="message.php">News</a>
            <a href="faq.html">FAQ</a>
            <a href="aide.html">Help</a>
            <a href="apropos.html">About Us</a>
        </div>
        <div class="navbar-right">
            <button class="create-btn">Create</button>
            <div class="user-profile">
                <div class="user-avatar">FF</div>
                <span class="user-name">Freaky-Faidi</span>
                ▼
            </div>
        </div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
    </nav>

    <main>
        <section class="signup-container">
            <h1>Create an Account</h1>
            <?php if ($message): ?>
                <p class="error-message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form class="signup-form" method="POST" action="">
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Sign Up</button>
            </form>
            <div class="additional-links">
                <p>Already have an account? <a href="login.php">Log in here</a>.</p>
            </div>
        </section>
    </main>

    <script>
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            const nav = document.getElementById('navMenu');
            nav.classList.toggle('show');
        });
    </script>
</body>
</html>