<?php
require_once 'db.php';

header('Content-Type: text/html; charset=UTF-8');

$customerId = $_GET['customer_id'] ?? '';
if (empty($customerId)) {
    echo '<div class="glass-panel" style="padding:20px;">No customer specified.</div>';
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT * FROM booking_details WHERE customer_ID = :cid ORDER BY booking_date DESC');
    $stmt->execute([':cid' => $customerId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo '<div class="glass-panel" style="padding:20px;">No bookings found for this traveler.</div>';
        exit();
    }

    echo '<div class="glass-panel" style="padding:10px; margin-top:12px;">';
    echo '<h3>Bookings for ' . htmlspecialchars($customerId) . '</h3>';
    echo '<table class="admin-table" style="width:100%; margin-top:8px;">';
    echo '<thead><tr><th>Booking ID</th><th>Visit Date</th><th>Ticket</th><th>Status</th><th>Reference</th></tr></thead>';
    echo '<tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['booking_ID']) . '</td>';
        echo '<td>' . htmlspecialchars($r['visit_date']) . '</td>';
        echo '<td>' . htmlspecialchars($r['ticket_type']) . '</td>';
        echo '<td>' . htmlspecialchars($r['booking_status']) . '</td>';
        echo '<td>' . htmlspecialchars($r['reference_number']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
} catch (PDOException $e) {
    echo '<div class="glass-panel" style="padding:20px;">Database error fetching bookings.</div>';
}

?>
