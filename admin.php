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

        // --- CUSTOMERS (TRAVELERS) CRUD ---
        if ($_POST['action'] === 'create_customer') {
            $full = trim($_POST['full_name'] ?? $_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? null;

            if (empty($full) || empty($email)) {
                $message = "⚠️ Missing name or email for new traveler.";
            } else {
                // generate a unique customer_ID
                $customerId = 'C' . str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
                $pwHash = $password ? password_hash($password, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO customer_info (customer_ID, full_name, email, password_hash) VALUES (:id, :full_name, :email, :hash)');
                $stmt->execute([':id' => $customerId, ':full_name' => $full, ':email' => $email, ':hash' => $pwHash]);
                $message = "✅ Traveler added: {$full} (ID: {$customerId}).";
            }
        }

        if ($_POST['action'] === 'update_customer') {
            $cid = trim($_POST['customer_id'] ?? '');
            $full = trim($_POST['full_name'] ?? $_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? null;

            if (empty($cid) || empty($full) || empty($email)) {
                $message = "⚠️ Missing required fields for traveler update.";
            } else {
                $params = [':full_name' => $full, ':email' => $email, ':id' => $cid];
                $sql = "UPDATE customer_info SET full_name = :full_name, email = :email";
                if (!empty($password)) {
                    $sql .= ", password_hash = :hash";
                    $params[':hash'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= " WHERE customer_ID = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $message = "✏️ Traveler {$cid} updated.";
            }
        }

        if ($_POST['action'] === 'delete_customer') {
            $cid = trim($_POST['customer_id'] ?? $_POST['id'] ?? '');
            if (empty($cid)) {
                $message = "⚠️ No traveler ID supplied for deletion.";
            } else {
                // Remove bookings first to keep referential integrity
                $stmt = $pdo->prepare('DELETE FROM booking_details WHERE customer_ID = :cid');
                $stmt->execute([':cid' => $cid]);
                $stmt = $pdo->prepare('DELETE FROM customer_info WHERE customer_ID = :cid');
                $stmt->execute([':cid' => $cid]);
                $message = "🗑️ Traveler {$cid} and their bookings removed.";
            }
        }
    } catch (\PDOException $e) {
        $message = "⚠️ Magic Failure: " . $e->getMessage();
    }
}

// Fetch current data for rendering
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

            <!-- Spell Bookings section removed per request -->

            <section id="customers" class="admin-section">
                <div class="section-header">
                    <h2 class="glowing-text">Registered Travelers</h2>
                    <button class="action-btn" id="summonAddCustomerModal" style="background: var(--eldar-green); color: white; margin-left:12px;">+ Add Traveler</button>
                </div>
                <div class="table-container glass-panel">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                            <tr class="customer-row" data-customer-id="<?php echo htmlspecialchars($c['customer_ID']); ?>">
                                <td><?php echo htmlspecialchars($c['customer_ID']); ?></td>
                                <td><?php echo htmlspecialchars($c['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td>
                                    <button class="action-btn edit-customer-trigger" data-customer-id="<?php echo htmlspecialchars($c['customer_ID']); ?>" data-full_name="<?php echo htmlspecialchars($c['full_name']); ?>" data-email="<?php echo htmlspecialchars($c['email']); ?>">Edit</button>
                                    <form method="POST" style="display:inline; margin-left:8px;" onsubmit="return confirm('Delete traveler and their bookings?');">
                                        <input type="hidden" name="action" value="delete_customer">
                                        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($c['customer_ID']); ?>">
                                        <button type="submit" class="action-btn" style="background: rgba(244,67,54,0.2); color:#ff8a80;">Delete</button>
                                    </form>
                                </td>
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

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;">
        <div class="glass-panel" style="padding:30px; width:420px; background: var(--eldar-purple-dark);">
            <h3 class="glowing-text" style="margin-bottom:20px;">Add Traveler</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_customer">
                <div style="margin-bottom:12px;">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Email</label>
                    <input type="email" name="email" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Password (optional)</label>
                    <input type="password" name="password" style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <button type="submit" class="action-btn">Add Traveler</button>
                <button type="button" onclick="closeModals()" class="action-btn" style="background:#555;">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="editCustomerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;">
        <div class="glass-panel" style="padding:30px; width:420px; background: var(--eldar-purple-dark);">
            <h3 class="glowing-text" style="margin-bottom:20px;">Edit Traveler</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_customer">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <div style="margin-bottom:12px;">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit_customer_fullname" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_customer_email" required style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="edit_customer_password" style="width:100%; padding:8px; margin-top:5px;">
                </div>
                <button type="submit" class="action-btn">Save Changes</button>
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

    <script src="admin.js?v=<?php echo time(); ?>"></script>
    <script>
        // Safe modal close utility (hides any modal that exists)
        function closeModals() {
            ['addModal','editModal','addCustomerModal','editCustomerModal'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
        }
    </script>
</body>
</html>