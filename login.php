<?php
// login.php
session_start();
require 'db.php'; // Your database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please provide both email and passphrase.'); window.history.back();</script>";
        exit();
    }

    // Hardcoded admin gatekeeper
    if ($email === 'dragonFursona@gmail.com') {
        $_SESSION['user_id'] = 'EK-001';
        $_SESSION['role'] = 'admin';
        $_SESSION['fullname'] = 'Aerion Targaryen';
        echo "<script>alert('Welcome Grand Wizard Aerion!'); window.location.href='admin.php';</script>";
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT customer_ID, full_name, password_hash FROM customer_info WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['customer_ID'];
            $_SESSION['fullname'] = $row['full_name'];
            $_SESSION['role'] = 'user';
            // If there's a pending booking in session, proceed to complete it
            if (!empty($_SESSION['pending_booking'])) {
                echo "<script>alert('Welcome back, " . addslashes($row['full_name']) . "! Redirecting to complete your booking.'); window.location.href='complete_booking.php';</script>";
                exit();
            }
            echo "<script>alert('Welcome back, " . addslashes($row['full_name']) . "!'); window.location.href='index.php';</script>";
            exit();
        }

        echo "<script>alert('Incorrect e-mail or passphrase.'); window.history.back();</script>";
        exit();
    } catch (PDOException $e) {
        die('Database Error: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal of Entry | Enchanted Kingdom</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Container narrowed down because we only show one box at a time */
        .auth-wrapper {
            max-width: 500px;
            margin: 150px auto 100px auto;
            padding: 0 20px;
        }

        /* Tab Layout */
        .auth-tabs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .tab-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 213, 79, 0.3);
            color: var(--text-light);
            padding: 12px 35px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        /* Highlighted/Selected Tab styling */
        .tab-btn.active, .tab-btn:hover {
            background-color: var(--eldar-purple);
            color: var(--eldar-gold);
            border-color: var(--eldar-gold);
            box-shadow: var(--eldar-gold-glow);
        }

        /* Hide boxes by default, show when active class is added */
        .auth-box {
            display: none;
            width: 100%;
        }

        .auth-box.active {
            display: block;
            animation: formFadeIn 0.4s ease forwards;
        }

        .auth-box h2 {
            color: var(--eldar-gold);
            margin-bottom: 25px;
            font-family: 'Georgia', serif;
            text-shadow: var(--eldar-gold-glow);
            text-align: center;
        }

        @keyframes formFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="stars-overlay"></div>

    <header class="navbar" id="navbar">
        <div class="logo">EK Magic</div>
        <nav>
            <ul class="nav-links">
                <li><a href="index.php#home">Realm</a></li>
                <li><a href="index.php#attractions">Wonders</a></li>
                <li><a href="index.php#tickets">Summon Tickets</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="auth-wrapper">
            <div class="auth-tabs">
                <button type="button" class="tab-btn active" id="tab-login" onclick="switchTab('login')">Log In</button>
                <button type="button" class="tab-btn" id="tab-register" onclick="switchTab('register')">Sign Up</button>
            </div>

            <div class="auth-container">
                <div id="login-box" class="auth-box glass-panel active">
                    <h2>Log In</h2>
                    <form id="loginForm" action="login.php" method="POST">
                        <div class="form-group">
                            <label for="login-email">Celestial Mail (Email)</label>
                            <input type="email" id="login-email" name="email" required placeholder="traveler@realm.com">
                        </div>
                        <div class="form-group">
                            <label for="login-password">Secret Passphrase</label>
                            <input type="password" id="login-password" name="password" required placeholder="Enter your password">
                        </div>
                        <button type="submit" class="submit-btn">Enter the Realm</button>
                    </form>
                </div>

                <div id="register-box" class="auth-box glass-panel">
                    <h2>Sign Up</h2>
                    <form id="registerForm" action="register.php" method="POST">
                        <div class="form-group">
                            <label for="reg-name">True Name (Full Name)</label>
                            <input type="text" id="reg-name" name="fullname" required placeholder="Eldar The Great">
                        </div>
                        <div class="form-group">
                            <label for="reg-email">Celestial Mail (Email)</label>
                            <input type="email" id="reg-email" name="email" required placeholder="traveler@realm.com">
                        </div>
                        <div class="form-group">
                            <label for="reg-password">Secret Passphrase</label>
                            <input type="password" id="reg-password" name="password" required placeholder="Create a password">
                        </div>
                        <button type="submit" class="submit-btn">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Enchanted Kingdom Fan Portal | Santa Rosa, Laguna, Philippines. May the magic be with you.</p>
    </footer>

    <script src="script.js"></script>

    <script>
        function switchTab(mode) {
            const loginBox = document.getElementById('login-box');
            const registerBox = document.getElementById('register-box');
            const tabLogin = document.getElementById('tab-login');
            const tabRegister = document.getElementById('tab-register');

            if (mode === 'login') {
                loginBox.classList.add('active');
                registerBox.classList.remove('active');
                tabLogin.classList.add('active');
                tabRegister.classList.remove('active');
            } else {
                registerBox.classList.add('active');
                loginBox.classList.remove('active');
                tabRegister.classList.add('active');
                tabLogin.classList.remove('active');
            }
        }
    </script>
</body>
</html>