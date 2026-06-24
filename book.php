<?php
session_start();
require_once 'db.php';

// Ensure logs directory exists for debugging booking submissions
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

function log_booking_debug($level, $message, $data = []) {
    global $logDir;
    $entry = [
        'time' => date('c'),
        'level' => $level,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'post' => $data,
    ];
    @file_put_contents($logDir . '/book_debug.log', json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept either traveler_* or name/email fields from different form versions
    $full_name = trim($_POST['traveler_name'] ?? $_POST['name'] ?? '');
    $email = trim($_POST['traveler_email'] ?? $_POST['email'] ?? '');
    $ticket_type = trim($_POST['ticket_type'] ?? 'General Admission');
    $visit_date = trim($_POST['visit_date'] ?? date('Y-m-d'));
    $adult_count = intval($_POST['adult_count'] ?? 1);
    $children_count = intval($_POST['children_count'] ?? 0);
    $senior_pwd_count = intval($_POST['senior_pwd_count'] ?? 0);
    $optional_services = trim($_POST['optional_services'] ?? 'None');
    $special_request = trim($_POST['special_request'] ?? 'None');
    $total_amount_php = floatval($_POST['total_amount_php'] ?? 0);

    if (empty($full_name) || empty($email)) {
        // Log missing fields for debugging before returning the message
        log_booking_debug('warning', 'Missing required booking fields', ['traveler_name' => $full_name, 'traveler_email' => $email, 'raw_post' => $_POST]);
        die('Please provide both your full name and email to book tickets.');
    }

    try {
        if (!empty($_SESSION['role']) && $_SESSION['role'] === 'user' && !empty($_SESSION['user_id'])) {
            $customerId = $_SESSION['user_id'];
        } else {
            $stmt = $pdo->prepare('SELECT customer_ID FROM customer_info WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customer && !empty($customer['customer_ID'])) {
                $customerId = $customer['customer_ID'];
            } else {
                $customerId = 'C' . str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
                $randomPassword = bin2hex(random_bytes(6));
                $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
                $insertCustomer = $pdo->prepare('INSERT INTO customer_info (customer_ID, full_name, email, password_hash) VALUES (:id, :full_name, :email, :hash)');
                $insertCustomer->execute([
                    ':id' => $customerId,
                    ':full_name' => $full_name,
                    ':email' => $email,
                    ':hash' => $passwordHash,
                ]);
            }
        }

        $bookingId = 'B' . str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        $referenceNumber = 'EK' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

        $sql = 'INSERT INTO booking_details (
                    booking_ID,
                    customer_ID,
                    ticket_type,
                    visit_date,
                    adult_count,
                    children_count,
                    senior_pwd_count,
                    optional_services,
                    reference_number,
                    total_amount_php,
                    special_request,
                    booking_status,
                    booking_date
                ) VALUES (
                    :booking_id,
                    :customer_id,
                    :ticket_type,
                    :visit_date,
                    :adult_count,
                    :children_count,
                    :senior_pwd_count,
                    :optional_services,
                    :reference_number,
                    :total_amount_php,
                    :special_request,
                    :booking_status,
                    NOW()
                )';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':booking_id' => $bookingId,
            ':customer_id' => $customerId,
            ':ticket_type' => $ticket_type,
            ':visit_date' => $visit_date,
            ':adult_count' => $adult_count,
            ':children_count' => $children_count,
            ':senior_pwd_count' => $senior_pwd_count,
            ':optional_services' => $optional_services,
            ':reference_number' => $referenceNumber,
            ':total_amount_php' => $total_amount_php,
            ':special_request' => $special_request,
            ':booking_status' => 'Pending',
        ]);

        echo "<script>
                alert('✨ Booking recorded! Reference: {$referenceNumber}');
                window.location.href = 'index.php';
              </script>";
        exit();
    } catch (PDOException $e) {
        // Log DB exception with POST snapshot for debugging
        log_booking_debug('error', 'Database error inserting booking', ['error' => $e->getMessage(), 'post' => $_POST]);
        die('Database Error. Please try again later.');
    }
}

header('Location: index.php');
exit();
?>