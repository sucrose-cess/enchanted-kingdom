<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit();
    }

    try {
        $stmt = $pdo->prepare('SELECT customer_ID FROM customer_info WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            echo "<script>alert('That email already exists. Please log in instead.'); window.history.back();</script>";
            exit();
        }

        $customerId = 'C' . str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $pdo->prepare('INSERT INTO customer_info (customer_ID, full_name, email, password_hash) VALUES (:id, :full_name, :email, :hash)');
        $insert->execute([
            ':id' => $customerId,
            ':full_name' => $full_name,
            ':email' => $email,
            ':hash' => $passwordHash,
        ]);

        $_SESSION['user_id'] = $customerId;
        $_SESSION['fullname'] = $full_name;
        $_SESSION['role'] = 'user';

        echo "<script>alert('Welcome, " . addslashes($full_name) . "! Your account is now created.'); window.location.href='index.php';</script>";
        exit();
    } catch (PDOException $e) {
        die('Database Error: ' . $e->getMessage());
    }
}

header('Location: login.php');
exit();
