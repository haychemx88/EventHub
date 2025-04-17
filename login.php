<?php
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

// Handle login POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect to message.php
            header("Location: message.php");
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Invalid email or password.";
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EventHub</title>
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

        /* Login Container */
        .login-container {
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

        .login-container h1 {
            margin-bottom: 30px;
            font-size: 2em;
            color: var(--accent);
        }

        .login-form input {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .login-form input:focus {
            border-color: var(--accent);
            outline: none;
        }

        .login-form button {
            background-color: var(--accent);
            border: none;
            width: 100%;
            padding: 15px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px;
            font-size: 1em;
            transition: all 0.3s;
            color: var(--dark);
            margin-top: 10px;
        }

        .login-form button:hover {
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
            margin: 0 10px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .additional-links a:hover {
            color: var(--accent-dark);
            text-decoration: underline;
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

            .navbar-right .user-name {
                display: none;
            }

            .login-container {
                margin-top: 120px;
                padding: 30px 20px;
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
            <a href="message.php">News</a>
            <a href="faq.html">FAQ</a>
            <a href="aide.html">Help</a>
            <a href="apropos.html">About Us</a>
        </div>
        <div class="navbar-right">
            <button class="create-btn">
                <i class="fas fa-plus"></i>
                Create
            </button>
            <div class="user-profile">
                <div class="user-avatar">FF</div>
                <span class="user-name">Log in</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </nav>

    <main>
        <section class="login-container">
            <h1>Login to EventHub</h1>
            <?php if ($message): ?>
                <p style="color:red;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form class="login-form" method="POST" action="">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="additional-links">
                <a href="#">Forgot Password?</a>
                <a href="Sign.php">Sign Up</a>
            </div>
        </section>
    </main>

    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navMenu = document.getElementById('navMenu');
        
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    </script>
</body>
</html>
