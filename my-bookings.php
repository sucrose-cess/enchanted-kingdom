<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Spellbook | Enchanted Kingdom</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .bookings-container {
            max-width: 1000px;
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
            color: var(--text-light);
            margin-top: 20px;
        }
        .bookings-table th, .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 213, 79, 0.2);
            text-align: left;
        }
        .bookings-table th {
            color: var(--eldar-gold);
            font-size: 1.1rem;
            text-transform: uppercase;
        }
        .bookings-table tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }
        .ticket-badge {
            background: rgba(255, 213, 79, 0.2);
            color: var(--eldar-gold);
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            border: 1px solid var(--eldar-gold);
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
                <li><a href="my-bookings.php" style="color: var(--eldar-gold);">My Spellbook</a></li>
                <li id="auth-nav-link"><a href="login.php">Sign In / Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="bookings-container glass-panel">
            <div class="bookings-header">
                <h2 class="glowing-text">Your Summoned Tickets</h2>
                <p>Present these mystical records at the front gates of the realm.</p>
            </div>

            <div id="empty-state" style="text-align: center; padding: 40px 20px; display: none;">
                <h3 id="empty-title" style="color: var(--eldar-gold); margin-bottom: 15px; font-size: 1.5rem;">Your Spellbook is Empty!</h3>
                <p id="empty-desc" style="color: var(--text-light); margin-bottom: 25px;">You haven't summoned any tickets for the Enchanted Kingdom yet.</p>
                <button id="empty-btn" class="submit-btn" style="width: auto; padding: 10px 30px; border-radius: 25px;">
                    Summon Tickets Now
                </button>
            </div>

            <div id="bookings-table-container" style="overflow-x: auto; display: none;">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Visit Date</th>
                            <th>Pass Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#EK-99281</td>
                            <td>July 15, 2026</td>
                            <td>VIP Magic Pass</td>
                            <td><span class="ticket-badge" style="color: #ffb74d;">Pending</span></td>
                            <td>
                                <button class="action-btn confirm-btn" onclick="alert('Ticket Confirmed! See you at the gates.')">Confirm</button>
                                <button class="action-btn cancel-btn" onclick="cancelTicket()">Cancel</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Enchanted Kingdom Fan Portal | Santa Rosa, Laguna, Philippines.</p>
    </footer>

    <script src="script.js"></script>
    <script>
        // FRONT-END UI LOGIC: Check User State and Show Correct Layout
        document.addEventListener('DOMContentLoaded', () => {
            const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
            const hasBookings = localStorage.getItem('hasBookings') === 'true';

            const emptyState = document.getElementById('empty-state');
            const tableContainer = document.getElementById('bookings-table-container');
            const emptyTitle = document.getElementById('empty-title');
            const emptyDesc = document.getElementById('empty-desc');
            const emptyBtn = document.getElementById('empty-btn');

            if (!isLoggedIn) {
                // If they are a guest who isn't logged in
                emptyState.style.display = 'block';
                emptyTitle.innerText = 'Wandering Traveler Detected!';
                emptyDesc.innerText = 'You must be signed in to view your Spellbook and manage your tickets.';
                emptyBtn.innerText = 'Sign In to the Realm';
                emptyBtn.onclick = () => window.location.href = 'login.php';

            } else if (!hasBookings) {
                // If they are logged in, but haven't bought anything yet
                emptyState.style.display = 'block';
                emptyTitle.innerText = 'Your Spellbook is Empty!';
                emptyDesc.innerText = 'You haven\\'t summoned any tickets for the Enchanted Kingdom yet.';
                emptyBtn.innerText = 'Summon Tickets Now';
                emptyBtn.onclick = () => window.location.href = 'index.php#tickets';

            } else {
                // If they are logged in AND have booked tickets
                tableContainer.style.display = 'block';
            }
        });

        // Function for the cancel button
        function cancelTicket() {
            if(confirm("Are you sure you want to cancel this booking spell?")) {
                // Remove the booking from front-end memory
                localStorage.setItem('hasBookings', 'false');
                alert("Your ticket has been cancelled.");
                window.location.reload(); // Refresh to show empty state
            }
        }
    </script>
</body>
</html>