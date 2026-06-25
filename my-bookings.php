<?php
// 1. START SESSION & DATABASE CONNECTION
session_start();
require_once 'db.php'; // provides $pdo

if (!isset($pdo)) {
    die("Database connection failed. Check db.php.");
}

// Pricing matrix
$prices = [
    'Regular Day Pass'  => ['adult' => 1050, 'child' => 850,  'senior' => 750],
    'Junior Day Pass'   => ['adult' => 800,  'child' => 650,  'senior' => 600],
    'Magical Twin Pass' => ['adult' => 1800, 'child' => 1400, 'senior' => 1200],
];

$isLoggedIn = isset($_SESSION['user_id']);
$bookings = [];

if ($isLoggedIn) {
    $customerId = $_SESSION['user_id'];

    // 2. HANDLE CRUD ACTIONS
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

        // --- ACTION: CREATE NEW BOOKING ---
        if ($_POST['action'] === 'create') {
            $ticket_type      = $_POST['ticket_type'];
            $visit_date       = $_POST['visit_date'];
            $adult_count      = intval($_POST['adult_count']);
            $children_count   = intval($_POST['children_count']);
            $senior_pwd_count = intval($_POST['senior_pwd_count']);
            $optional_services = $_POST['optional_services'] ?? '';
            $special_request  = $_POST['special_request'] ?? '';
            $bookingId = 'B' . str_pad(mt_rand(10000,99999),5,'0',STR_PAD_LEFT);
            $reference_number = 'REF-' . strtoupper(uniqid());
            $booking_status   = 'Pending';
            $booking_date     = date('Y-m-d H:i:s');

            $rate = $prices[$ticket_type] ?? $prices['Regular Day Pass'];
            $total_amount_php = ($adult_count * $rate['adult']) + ($children_count * $rate['child']) + ($senior_pwd_count * $rate['senior']);

            $stmt = $pdo->prepare("
                INSERT INTO booking_details (
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
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $stmt->execute([
                $customerId,
                $ticket_type,
                $visit_date,
                $adult_count,
                $children_count,
                $senior_pwd_count,
                $optional_services,
                $reference_number,
                $total_amount_php,
                $special_request,
                $booking_status,
                $booking_date
            ]);
            header("Location: my-bookings.php?msg=created");
            exit;
        }


        // --- ACTION: CONFIRM BOOKING ---
        if ($_POST['action'] === 'confirm') {
            $bookingId = $_POST['booking_id'];
            $stmt = $pdo->prepare("UPDATE booking_details SET booking_status = 'Confirmed' WHERE booking_ID = ? AND customer_ID = ?");
            $stmt->execute([$bookingId, $customerId]);
            header("Location: my-bookings.php?msg=confirmed");
            exit;
        }

        // --- ACTION: FAST CANCEL ---
        if ($_POST['action'] === 'cancel') {
            $bookingId = $_POST['booking_id'];
            $stmt = $pdo->prepare("UPDATE booking_details SET booking_status = 'Cancelled' WHERE booking_ID = ? AND customer_ID = ?");
            $stmt->execute([$bookingId, $customerId]);
            header("Location: my-bookings.php?msg=cancelled");
            exit;
        }

        // --- ACTION: UPDATE/EDIT BOOKING DETAILS ---
        elseif ($_POST['action'] === 'update_details') {
            $bookingId        = $_POST['booking_id'];
            $ticket_type      = $_POST['ticket_type'];
            $visit_date       = $_POST['visit_date'];
            $adult_count      = intval($_POST['adult_count']);
            $children_count   = intval($_POST['children_count']);
            $senior_pwd_count = intval($_POST['senior_pwd_count']);
            $special_request  = $_POST['special_request'];

            $rate = $prices[$ticket_type] ?? $prices['Regular Day Pass'];
            $total_amount_php = ($adult_count * $rate['adult']) + ($children_count * $rate['child']) + ($senior_pwd_count * $rate['senior']);

            $stmt = $pdo->prepare("UPDATE booking_details SET ticket_type = ?, visit_date = ?, adult_count = ?, children_count = ?, senior_pwd_count = ?, total_amount_php = ?, special_request = ? WHERE booking_ID = ? AND customer_ID = ?");
            $stmt->execute([$ticket_type, $visit_date, $adult_count, $children_count, $senior_pwd_count, $total_amount_php, $special_request, $bookingId, $customerId]);

            header("Location: my-bookings.php?msg=updated");
            exit;
        }

        // --- ACTION: DELETE/PURGE RECORD ---
        elseif ($_POST['action'] === 'delete') {
            $bookingId = $_POST['booking_id'];
            $stmt = $pdo->prepare("DELETE FROM booking_details WHERE booking_ID = ? AND customer_ID = ?");
            $stmt->execute([$bookingId, $customerId]);
            header("Location: my-bookings.php?msg=deleted");
            exit;
        }
    }

    // 3. READ: Fetch this customer's bookings
    $stmt = $pdo->prepare("SELECT * FROM booking_details WHERE customer_ID = ? ORDER BY booking_date DESC");
    $stmt->execute([$customerId]);
    $bookings = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Spellbook | Enchanted Kingdom</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .bookings-container {
            max-width: 1200px;
            margin: 150px auto 100px auto;
            padding: 40px;
        }
        .bookings-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-light, #fff);
            margin-top: 20px;
        }
        .bookings-table th, .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 213, 79, 0.2);
            text-align: left;
        }
        .bookings-table th {
            color: var(--eldar-gold, #ffd54f);
            font-size: 1.1rem;
            text-transform: uppercase;
        }
        .bookings-table tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }
        .ticket-badge {
            background: rgba(255, 213, 79, 0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            border: 1px solid var(--eldar-gold, #ffd54f);
            display: inline-block;
        }
        .action-form {
            display: inline-block;
            margin: 0 2px;
        }
        .action-btn {
            padding: 6px 15px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: bold;
            border: none;
        }
        .confirm-btn { background-color: #4caf50; color: white; }
        .edit-btn { background-color: #2196f3; color: white; }
        .cancel-btn { background-color: #ff9800; color: white; }
        .delete-btn { background-color: #f44336; color: white; }

        .create-btn-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .create-btn {
            background-color: var(--eldar-gold, #ffd54f);
            color: #111;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .alert-banner {
            background: rgba(255, 213, 79, 0.1);
            border: 1px solid var(--eldar-gold, #ffd54f);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            color: #ffd54f;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background: #222;
            color: #fff;
            margin: 5% auto;
            padding: 30px;
            border: 2px solid var(--eldar-gold, #ffd54f);
            width: 90%; max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(255,213,79,0.3);
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-content h3 { margin-top: 0; color: var(--eldar-gold, #ffd54f); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: #ccc; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #555;
            background: #333; color: white; box-sizing: border-box;
        }
        .price-hint {
            font-size: 0.78rem;
            color: #aaa;
            margin: 4px 0 0 0;
        }
        .modal-actions { text-align: right; margin-top: 20px; }
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
                <li><a href="my-bookings.php" style="color: var(--eldar-gold);">My Spellbook</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="logout.php">Sign Out (<?php echo htmlspecialchars($_SESSION['fullname']); ?>)</a></li>
                <?php else: ?>
                    <li id="auth-nav-link"><a href="login.php">Sign In / Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="bookings-container glass-panel">
            <div class="bookings-header">
                <h2 class="glowing-text">Your Summoned Tickets</h2>
                <p>Present these mystical records at the gates of the realm.</p>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert-banner">
                    <?php
                        if($_GET['msg'] === 'created') echo "✨ Ticket successfully summoned into existence!";
                        if($_GET['msg'] === 'confirmed') echo "✅ Booking magic finalized and confirmed!";
                        if($_GET['msg'] === 'cancelled') echo "⚡ Booking spell cancelled successfully.";
                        if($_GET['msg'] === 'updated') echo "🔮 Booking properties transmuted and updated.";
                        if($_GET['msg'] === 'deleted') echo "🗑️ Record purged cleanly from the scrolls.";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!$isLoggedIn): ?>
                <div id="empty-state" style="text-align: center; padding: 40px 20px;">
                    <h3 style="color: var(--eldar-gold); margin-bottom: 15px; font-size: 1.5rem;">Wandering Traveler Detected!</h3>
                    <p style="color: var(--text-light); margin-bottom: 25px;">You must be signed in to view your Spellbook and manage your tickets.</p>
                    <button class="create-btn" style="width: auto; padding: 10px 30px; border-radius: 25px;" onclick="window.location.href='login.php'">
                        Sign In to the Realm
                    </button>
                </div>

            <?php elseif (empty($bookings)): ?>
                <div id="empty-state" style="text-align: center; padding: 40px 20px;">
                    <h3 style="color: var(--eldar-gold); margin-bottom: 15px; font-size: 1.5rem;">Your Spellbook is Empty!</h3>
                    <p style="color: var(--text-light); margin-bottom: 25px;">You haven't summoned any tickets for the Enchanted Kingdom yet.</p>
                    <button class="create-btn" onclick="openCreateModal()">+ Quick Summon Ticket</button>
                </div>

            <?php else: ?>
                <div class="create-btn-container">
                    <span style="color: #aaa;">Scroll details or manage spells below:</span>
                    <button class="create-btn" onclick="openCreateModal()">+ Quick Summon Ticket</button>
                </div>

                <div id="bookings-table-container" style="overflow-x: auto;">
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Visit Date</th>
                                <th>Pass Type</th>
                                <th>Total Guests</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $row): ?>
                                <?php
                                    $totalGuests = intval($row['adult_count']) + intval($row['children_count']) + intval($row['senior_pwd_count']);
                                    $badgeColor = '#ffb74d';
                                    if ($row['booking_status'] === 'Confirmed') $badgeColor = '#81c784';
                                    if ($row['booking_status'] === 'Cancelled') $badgeColor = '#e57373';
                                ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($row['reference_number'] ?: $row['booking_ID']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['visit_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ticket_type']); ?></td>
                                    <td><?php echo $totalGuests; ?> Guest(s)</td>
                                    <td>₱<?php echo number_format($row['total_amount_php']); ?></td>
                                    <td>
                                        <span class="ticket-badge" style="color: <?php echo $badgeColor; ?>; border-color: <?php echo $badgeColor; ?>;">
                                            <?php echo htmlspecialchars($row['booking_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['booking_status'] === 'Pending'): ?>
                                            <form class="action-form" method="POST">
                                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['booking_ID']); ?>">
                                                <input type="hidden" name="action" value="confirm">
                                                <button type="submit" class="action-btn confirm-btn">Confirm</button>
                                            </form>

                                            <button class="action-btn edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>

                                            <form class="action-form" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking spell?');">
                                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['booking_ID']); ?>">
                                                <input type="hidden" name="action" value="cancel">
                                                <button type="submit" class="action-btn cancel-btn">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <form class="action-form" method="POST" onsubmit="return confirm('Purge this dead record history entirely?');">
                                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['booking_ID']); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="action-btn delete-btn">Purge</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </section>
    </main>

    <!-- CREATE MODAL -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h3>✨ Summon New Magic Ticket</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Pass / Ticket Type</label>
                    <select name="ticket_type" id="create_ticket_type" onchange="updatePriceHint('create')" required>
                        <option value="Regular Day Pass">Regular Day Pass</option>
                        <option value="Junior Day Pass">Junior Day Pass</option>
                        <option value="Magical Twin Pass">Magical Twin Pass</option>
                    </select>
                    <p class="price-hint" id="create_price_hint">Adult ₱1,050 &nbsp;|&nbsp; Child ₱850 &nbsp;|&nbsp; Senior/PWD ₱750</p>
                </div>
                <div class="form-group">
                    <label>Visit Date</label>
                    <input type="date" name="visit_date" required>
                </div>
                <div class="form-group">
                    <label>Adult Count</label>
                    <input type="number" name="adult_count" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label>Children Count</label>
                    <input type="number" name="children_count" value="0" min="0" required>
                </div>
                <div class="form-group">
                    <label>Senior / PWD Count</label>
                    <input type="number" name="senior_pwd_count" value="0" min="0" required>
                </div>
                <div class="form-group">
                    <label>Special Request</label>
                    <textarea name="special_request" rows="2" placeholder="Dietary adjustments, physical access layouts..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="action-btn" onclick="closeModal('createModal')" style="background:#555; color:#fff;">Close</button>
                    <button type="submit" class="action-btn edit-btn">Cast Summon</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>🔮 Alter Booking Spell Properties</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_details">
                <input type="hidden" name="booking_id" id="edit_booking_id">

                <div class="form-group">
                    <label>Pass / Ticket Type</label>
                    <select name="ticket_type" id="edit_ticket_type" onchange="updatePriceHint('edit')" required>
                        <option value="Regular Day Pass">Regular Day Pass</option>
                        <option value="Junior Day Pass">Junior Day Pass</option>
                        <option value="Magical Twin Pass">Magical Twin Pass</option>
                    </select>
                    <p class="price-hint" id="edit_price_hint">Adult ₱1,050 &nbsp;|&nbsp; Child ₱850 &nbsp;|&nbsp; Senior/PWD ₱750</p>
                </div>
                <div class="form-group">
                    <label>Visit Date</label>
                    <input type="date" name="visit_date" id="edit_visit_date" required>
                </div>
                <div class="form-group">
                    <label>Adult Count</label>
                    <input type="number" name="adult_count" id="edit_adult_count" min="1" required>
                </div>
                <div class="form-group">
                    <label>Children Count</label>
                    <input type="number" name="children_count" id="edit_children_count" min="0" required>
                </div>
                <div class="form-group">
                    <label>Senior / PWD Count</label>
                    <input type="number" name="senior_pwd_count" id="edit_senior_pwd_count" min="0" required>
                </div>
                <div class="form-group">
                    <label>Special Request</label>
                    <textarea name="special_request" id="edit_special_request" rows="2"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="action-btn" onclick="closeModal('editModal')" style="background:#555; color:#fff;">Close</button>
                    <button type="submit" class="action-btn edit-btn">Save Transmutation</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Enchanted Kingdom Fan Portal | Santa Rosa, Laguna, Philippines.</p>
    </footer>

    <script>
        const priceMap = {
            'Regular Day Pass':  { adult: 1050, child: 850,  senior: 750 },
            'Junior Day Pass':   { adult: 800,  child: 650,  senior: 600 },
            'Magical Twin Pass': { adult: 1800, child: 1400, senior: 1200 },
        };

        function formatHint(type) {
            const p = priceMap[type];
            return `Adult ₱${p.adult.toLocaleString()} &nbsp;|&nbsp; Child ₱${p.child.toLocaleString()} &nbsp;|&nbsp; Senior/PWD ₱${p.senior.toLocaleString()}`;
        }

        function updatePriceHint(prefix) {
            const select = document.getElementById(prefix + '_ticket_type');
            const hint   = document.getElementById(prefix + '_price_hint');
            hint.innerHTML = formatHint(select.value);
        }

        function openCreateModal() {
            document.getElementById('createModal').style.display = 'block';
            updatePriceHint('create');
        }

        function openEditModal(bookingData) {
            document.getElementById('edit_booking_id').value       = bookingData.booking_ID;
            document.getElementById('edit_ticket_type').value      = bookingData.ticket_type;
            document.getElementById('edit_visit_date').value       = bookingData.visit_date;
            document.getElementById('edit_adult_count').value      = bookingData.adult_count;
            document.getElementById('edit_children_count').value   = bookingData.children_count;
            document.getElementById('edit_senior_pwd_count').value = bookingData.senior_pwd_count;
            document.getElementById('edit_special_request').value  = bookingData.special_request;
            updatePriceHint('edit');
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>