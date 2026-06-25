// ==========================================
// 🔧 ID PARSING UTILITY FOR RD-### FORMAT
// ==========================================
function parseAttractionId(idInput) {
    if (typeof idInput === 'string' && idInput.startsWith('RD-')) {
        return parseInt(idInput.substring(3), 10);
    }
    return parseInt(idInput, 10);
}

// ==========================================
// 📅 CURRENT DATE
// ==========================================
const dateDisplay = document.getElementById('currentDate');
const options = {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
};

if (dateDisplay) {
    dateDisplay.innerText = new Date().toLocaleDateString('en-US', options);
}

// ==========================================
// 📌 SIDEBAR NAVIGATION
// ==========================================
const navItems = document.querySelectorAll('.sidebar-nav li');
const sections = document.querySelectorAll('.admin-section');

navItems.forEach(item => {
    item.addEventListener('click', function () {

        const targetId = this.getAttribute('data-target');

        if (!targetId) {
            alert("Logging out of the realm...");
            window.location.href = "index.php";
            return;
        }

        navItems.forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');

        sections.forEach(section =>
            section.classList.remove('active-section')
        );

        const targetSection = document.getElementById(targetId);

        if (targetSection) {
            targetSection.classList.add('active-section');
        }
    });
});

// ==========================================
// 📦 MODALS
// ==========================================
const addModal = document.getElementById('addModal');
const editModal = document.getElementById('editModal');
const addCustomerModal = document.getElementById('addCustomerModal');
const editCustomerModal = document.getElementById('editCustomerModal');
const editBookingModal = document.getElementById('editBookingModal');

const summonAddModalBtn = document.getElementById('summonAddModal');
const summonAddCustomerModalBtn = document.getElementById('summonAddCustomerModal');

// ==========================================
// ➕ OPEN ADD ATTRACTION MODAL
// ==========================================
if (summonAddModalBtn) {
    summonAddModalBtn.addEventListener('click', () => {
        addModal.style.display = 'flex';
    });
}

// ==========================================
// ➕ OPEN ADD CUSTOMER MODAL
// ==========================================
if (summonAddCustomerModalBtn) {
    summonAddCustomerModalBtn.addEventListener('click', () => {
        if (addCustomerModal) {
            addCustomerModal.style.display = 'flex';
        }
    });
}

// ==========================================
// 🎡 EDIT ATTRACTION
// ==========================================
document.querySelectorAll('.edit-trigger').forEach(button => {
    button.addEventListener('click', function () {

        const rawId = this.getAttribute('data-id');
        const numericId = parseAttractionId(rawId);

        document.getElementById('edit_id').value = numericId;
        document.getElementById('edit_name').value =
            this.getAttribute('data-name');

        document.getElementById('edit_wait').value =
            this.getAttribute('data-wait');

        document.getElementById('edit_status').value =
            this.getAttribute('data-status');

        if (editModal) {
            editModal.style.display = 'flex';
        }
    });
});

// ==========================================
// 🎟️ EDIT BOOKING STATUS
// ==========================================
document.querySelectorAll('.edit-booking-trigger').forEach(button => {

    button.addEventListener('click', function () {

        const bookingId = this.getAttribute('data-booking-id');
        const bookingStatus = this.getAttribute('data-booking-status');

        const idField = document.getElementById('edit_booking_id');
        const statusField = document.getElementById('edit_booking_status');

        if (idField) {
            idField.value = bookingId;
        }

        if (statusField) {
            statusField.value = bookingStatus;
        }

        if (editBookingModal) {
            editBookingModal.style.display = 'flex';
        }
    });

});

// ==========================================
// 👤 EDIT CUSTOMER
// ==========================================
document.querySelectorAll('.edit-customer-trigger').forEach(btn => {

    btn.addEventListener('click', function () {

        const cid = this.getAttribute('data-customer-id');
        const fullname = this.getAttribute('data-full_name');
        const email = this.getAttribute('data-email');

        const idEl = document.getElementById('edit_customer_id');
        const nameEl = document.getElementById('edit_customer_fullname');
        const emailEl = document.getElementById('edit_customer_email');
        const passEl = document.getElementById('edit_customer_password');

        if (idEl) idEl.value = cid;
        if (nameEl) nameEl.value = fullname || '';
        if (emailEl) emailEl.value = email || '';
        if (passEl) passEl.value = '';

        if (editCustomerModal) {
            editCustomerModal.style.display = 'flex';
        }
    });

});

// ==========================================
// 🗑️ DELETE SAFETY CHECK
// ==========================================
document.querySelectorAll('.banish-trigger').forEach(button => {

    button.addEventListener('click', function (e) {

        let targetId = this.getAttribute('data-id');

        if (this.closest('form')) {
            return;
        }

        targetId = parseAttractionId(targetId);

        if (!targetId || targetId === 0 || isNaN(targetId)) {

            e.preventDefault();

            alert(
                "⚠️ Critical Spell Failure: Invalid tracking metric index."
            );

            return;
        }
    });

});

// ==========================================
// ❌ CLOSE ALL MODALS
// ==========================================
function closeModals() {

    if (addModal) addModal.style.display = 'none';
    if (editModal) editModal.style.display = 'none';
    if (addCustomerModal) addCustomerModal.style.display = 'none';
    if (editCustomerModal) editCustomerModal.style.display = 'none';
    if (editBookingModal) editBookingModal.style.display = 'none';
}

window.closeModals = closeModals;

// ==========================================
// 🖱️ CLOSE WHEN CLICKING OUTSIDE
// ==========================================
window.addEventListener('click', (e) => {

    if (
        e.target === addModal ||
        e.target === editModal ||
        e.target === addCustomerModal ||
        e.target === editCustomerModal ||
        e.target === editBookingModal
    ) {
        closeModals();
    }
});

// ==========================================
// 📋 CUSTOMER BOOKINGS VIEW
// ==========================================
document.querySelectorAll('.customer-row').forEach(row => {

    row.style.cursor = 'pointer';

    row.addEventListener('click', function () {

        const cid = this.getAttribute('data-customer-id');
        const container = document.getElementById('customerBookingsContainer');

        if (!cid || !container) return;

        container.innerHTML =
            '<div class="glass-panel" style="padding:20px;">Loading bookings...</div>';

        fetch(
            'customer_bookings.php?customer_id=' +
            encodeURIComponent(cid)
        )
            .then(r => r.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML =
                    '<div class="glass-panel" style="padding:20px;">Error loading bookings.</div>';
                console.error(err);
            });

    });

});