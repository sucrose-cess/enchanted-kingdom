<?php
session_start();
require_once 'db.php';

// Helper logging from book.php style
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
        'data' => $data,
    ];
    @file_put_contents($logDir . '/book_debug.log', json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    // Not authenticated — redirect to login
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['pending_booking']) || !is_array($_SESSION['pending_booking'])) {
    echo "<script>alert('No pending booking found.'); window.location.href='index.php';</script>";
    exit();
}

$post = $_SESSION['pending_booking'];

// Map fields (same logic as in book.php)
$full_name = trim($post['traveler_name'] ?? $post['name'] ?? '');
$email = trim($post['traveler_email'] ?? $post['email'] ?? '');
$ticket_type = trim($post['ticket_type'] ?? 'General Admission');
$visit_date = trim($post['visit_date'] ?? date('Y-m-d'));
$adult_count = intval($post['adult_count'] ?? 1);
$children_count = intval($post['children_count'] ?? 0);
$senior_pwd_count = intval($post['senior_pwd_count'] ?? 0);
$optional_services = trim($post['optional_services'] ?? 'None');
$special_request = trim($post['special_request'] ?? 'None');
$total_amount_php = floatval($post['total_amount_php'] ?? 0);

try {
    // Use logged-in user as customer
    $customerId = $_SESSION['user_id'];

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

    log_booking_debug('info', 'Completed pending booking', ['booking_id' => $bookingId, 'customer_id' => $customerId, 'post' => $post]);

    // Clear pending booking
    unset($_SESSION['pending_booking']);

    echo "<script>alert('✨ Booking completed! Reference: {$referenceNumber}'); window.location.href='index.php';</script>";
    exit();
} catch (PDOException $e) {
    log_booking_debug('error', 'Database error completing pending booking', ['error' => $e->getMessage(), 'post' => $post]);
    echo "<script>alert('Database Error. Please try again later.'); window.location.href='index.php';</script>";
    exit();
}

?>
