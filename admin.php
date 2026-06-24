<?php
require_once 'db.php';

$message = "";


function parseAttractionId($idInput) {
    if (is_string($idInput) && strpos($idInput, 'RD-') === 0) {
        return intval(substr($idInput, 3));
    }
    return intval($idInput);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // --- ATTRACTIONS CRUD PROCESSORS ---
        if ($_POST['action'] === 'create') {
            $name = trim($_POST['attraction_name']);
            $wait = intval($_POST['wait_time']);
            $status = trim($_POST['status']);
            
            if (!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO attractions (attraction_name, estimated_wait_time_min, status) VALUES (:name, :wait, :status)");
                $stmt->execute([':name' => $name, ':wait' => $wait, ':status' => $status]);
                header("Location: admin.php");
                exit;
            }
        }
        
        if ($_POST['action'] === 'update') {

            $id = parseAttractionId(trim($_POST['attractions_ID']));
            $name = trim($_POST['attraction_name']);
            $wait = trim($_POST['wait_time']);
            $status = trim($_POST['status']);

            if (!empty($id) && !empty($name)) {

                $stmt = $pdo->prepare("
                    UPDATE attractions
                    SET
                        attraction_name = :name,
                        estimated_wait_time_min = :wait,
                        status = :status
                    WHERE attractions_ID = :id
                ");

                $stmt->execute([
                    ':name' => $name,
                    ':wait' => $wait,
                    ':status' => $status,
                    ':id' => $id
                ]);

                    header("Location: admin.php");
                    exit;
            }
        }

       if ($_POST['action'] === 'delete') {

            $id = parseAttractionId(trim($_POST['id'] ?? ''));
            $name = trim($_POST['name'] ?? '');

            if ($id > 0) {
                $stmt = $pdo->prepare("
                    DELETE FROM attractions
                    WHERE attractions_ID = :id
                ");
                $stmt->execute([':id' => $id]);
            } elseif (!empty($name)) {
                $stmt = $pdo->prepare("
                    DELETE FROM attractions
                    WHERE attraction_name = :name
                ");
                $stmt->execute([':name' => $name]);
            } else {
                $message = "⚠️ Could not banish attraction: Missing ID and name.";
                goto skip_redirect;
            }

            header("Location: admin.php");
            exit;

            skip_redirect:
}

        // --- BOOKINGS CRUD PROCESSORS ---
        if ($_POST['action'] === 'update_booking') {
            $id = trim($_POST['booking_id']);
            $status = trim($_POST['booking_status']);

            $stmt = $pdo->prepare("UPDATE booking_details SET booking_status = :status WHERE booking_ID = :id");
            $stmt->execute([':status' => $status, ':id' => $id]);
            $message = "📜 Booking spell status updated successfully.";
        }
        
        if ($_POST['action'] === 'delete_booking') {
            $id = trim($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM booking_details WHERE booking_ID = :id");
            $stmt->execute([':id' => $id]);
            $message = "💥 Booking log wiped from history.";
        }
    } catch (\PDOException $e) {
        $message = "⚠️ Magic Failure: " . $e->getMessage();
    }
}

$attractions = $pdo->query("SELECT * FROM attractions ORDER BY attractions_ID ASC")->fetchAll();
$customers = $pdo->query("SELECT * FROM customer_info ORDER BY full_name ASC")->fetchAll();
$bookings = $pdo->query("SELECT b.*, c.full_name AS customer_name, c.email AS customer_email FROM booking_details b LEFT JOIN customer_info c ON b.customer_ID = c.customer_ID ORDER BY booking_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EK Admin Realm | Management</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="stars-overlay"></div>

    <div class="admin-container">
        <aside class="sidebar glass-panel">
            <div class="sidebar-header">
                <h2 class="logo">🔮 EK Admin</h2>
                <p class="admin-role">Grand Wizard (Super Admin)</p>
            </div>
            <ul class="sidebar-nav">
                <li class="active" data-target="dashboard">🌌 Realm Overview</li>
                <li data-target="customers">🧙‍♂️ Travelers</li>
                <li data-target="bookings">📜 Spell Bookings</li>
                <li data-target="attractions">🎡 Attractions</li>
                <li>🚪 Logout</li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="admin-header glass-panel">
                <h2>Welcome back, Aerion!</h2>
                <div class="date-display" id="currentDate"></div>
            </header>

            <?php if(!empty($message)): ?>
                <div style="background: rgba(255, 213, 79, 0.2); border: 1px solid var(--eldar-gold); padding: 15px; margin-bottom: 20px; border-radius: 8px; color: var(--eldar-gold);">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <section id="dashboard" class="admin-section active-section">
                <div class="stats-grid">
                    <div class="stat-card glass-panel">
                        <h3>Total Travelers Today</h3>
                        <div class="stat-number"><?php echo count($bookings); ?></div>
                    </div>
                    <div class="stat-card glass-panel">
                        <h3>Active Wonders</h3>
                        <div class="stat-number"><?php echo count($attractions); ?></div>
                    </div>
                </div>
                <div id="customerBookingsContainer"></div>
            </section>

            <section id="bookings" class="admin-section">
                <div class="section-header">
                    <h2 class="glowing-text">Spell Bookings Scroll</h2>
                </div>
                <div class="table-container glass-panel">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Traveler</th>
                                <th>Email</th>
                                <th>Visit Date</th>
                                <th>Ticket</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['booking_ID']); ?></td>
                                <td><?php echo htmlspecialchars($b['customer_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($b['customer_email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($b['visit_date']); ?></td>
                                <td><?php echo htmlspecialchars($b['ticket_type']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($b['booking_status']); ?>">
                                        <?php echo htmlspecialchars($b['booking_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn edit-btn edit-booking-trigger" 
                                            data-id="<?php echo $b['booking_ID']; ?>"
                                            data-status="<?php echo htmlspecialchars($b['booking_status']); ?>">Modify</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Banish this booking record?');">
                                        <input type="hidden" name="action" value="delete_booking">
                                        <input type="hidden" name="id" value="<?php echo $b['booking_ID']; ?>">
                                        <button type="submit" class="action-btn banish-trigger" data-id="<?php echo $b['booking_ID']; ?>" style="background: rgba(244,67,54,0.2); color:#ff8a80;">Banish</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="customers" class="admin-section">
                <div class="section-header">
                    <h2 class="glowing-text">Registered Travelers</h2>
                </div>
                <div class="table-container glass-panel">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                            <tr class="customer-row" data-customer-id="<?php echo htmlspecialchars($c['customer_ID']); ?>">
                                <td><?php echo htmlspecialchars($c['customer_ID']); ?></td>
                                <td><?php echo htmlspecialchars($c['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="attractions" class="admin-section">
                <div class="section-header">
                    <h2 class="glowing-text">Active Realm Attractions</h2>
                    <button class="action-btn" id="summonAddModal" style="background: var(--eldar-green); color: white;">+ Materialize Attraction</button>
                </div>
                <div class="table-container glass-panel">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Attraction Name</th>
                                <th>Wait Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attractions as $a):
                                $aId = $a['attractions_ID'] ?? '';
                            ?>
                            <tr>
                                <td>RD-<?php echo str_pad($aId, 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($a['attraction_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($a['estimated_wait_time_min'] ?? ''); ?> mins</td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($a['status'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($a['status'] ?? ''); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn edit-btn edit-trigger"
                                            data-id="<?php echo $aId; ?>"
                                            data-name="<?php echo htmlspecialchars($a['attraction_name'] ?? ''); ?>"
                                            data-wait="<?php echo $a['estimated_wait_time_min'] ?? ''; ?>"
                                            data-status="<?php echo htmlspecialchars($a['status'] ?? ''); ?>">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Banish this wonder?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $aId; ?>">
                                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($a['attraction_name'] ?? ''); ?>">
                                        <button type="submit" class="action-btn banish-trigger" data-id="<?php echo $aId; ?>" style="background: rgba(244,67,54,0.2); color:#ff8a80;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;">
        <div class="glass-panel" style="padding:30px; width:400px; background: var(--eldar-purple-dark);">
            <h3 class="glowing-text" style="margin-bottom:20px;">Materialize Attraction</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div style="margin-bottom:15px;">
                    <label>Name</label>
                    <input type="text" name="attraction_name" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>Wait Time (mins)</label>
                    <input type="number" name="wait_time" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>Status</label>
                    <select name="status" style="width:100%; padding:8px; margin-top:5px; background:#000; color:#fff;">
                        <option value="Open">Open</option>
                        <option value="Maintenance">Under Maintenance</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <button type="submit" class="action-btn">Cast Spell</button>
                <button type="button" onclick="closeModals()" class="action-btn" style="background:#555;">Cancel</button>
            </form>
        </div>
    </div>

    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;">
        <div class="glass-panel" style="padding:30px; width:400px; background: var(--eldar-purple-dark);">
            <h3 class="glowing-text" style="margin-bottom:20px;">Modify Attraction</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="attractions_ID" id="edit_id">
                <div style="margin-bottom:15px;">
                    <label>Name</label>
                    <input type="text" name="attraction_name" id="edit_name" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>Wait Time (mins)</label>
                    <input type="number" name="wait_time" id="edit_wait" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>Status</label>
                    <select name="status" id="edit_status" style="width:100%; padding:8px; margin-top:5px; background:#000; color:#fff;">
                        <option value="Open">Open</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <button type="submit" class="action-btn">Apply Transmutation</button>
                <button type="button" onclick="closeModals()" class="action-btn" style="background:#555;">Cancel</button>
            </form>
        </div>
    </div>

    <div id="editBookingModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;">
        <div class="glass-panel" style="padding:30px; width:400px; background: var(--eldar-purple-dark);">
            <h3 class="glowing-text" style="margin-bottom:20px;">Modify Booking Scroll</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_booking">
                <input type="hidden" name="booking_id" id="edit_booking_id">
                <div style="margin-bottom:15px;">

                <button type="submit" class="action-btn">Update Log</button>
                <button type="button" onclick="closeModals()" class="action-btn" style="background:#555;">Cancel</button>
            </form>
        </div>
    </div>

    <script src="admin.js?v=<?php echo time(); ?>"></script>
    <script>
        const addBookingModal = document.getElementById('editBookingModal');
        function closeModals() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editBookingModal').style.display = 'none';
        }
    </script>
</body>
</html>