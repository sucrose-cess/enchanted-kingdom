// ==========================================
// 🔧 ID PARSING UTILITY FOR RD-### FORMAT
// ==========================================
function parseAttractionId(idInput) {
    // If it's RD-### format, extract the numeric part
    if (typeof idInput === 'string' && idInput.startsWith('RD-')) {
        return parseInt(idInput.substring(3), 10);
    }
    // Otherwise, return as-is (should be numeric)
    return parseInt(idInput, 10);
}

// Display Current Date in Header
const dateDisplay = document.getElementById('currentDate');
const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
dateDisplay.innerText = new Date().toLocaleDateString('en-US', options);

// Sidebar Tab Switching Logic
const navItems = document.querySelectorAll('.sidebar-nav li');
const sections = document.querySelectorAll('.admin-section');

navItems.forEach(item => {
    item.addEventListener('click', function() {
        navItems.forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');

        sections.forEach(section => section.classList.remove('active-section'));

        const targetId = this.getAttribute('data-target');
        const targetSection = document.getElementById(targetId);
        
        if (targetSection) {
            targetSection.classList.add('active-section');
        } else {
            alert("Logging out of the realm...");
            window.location.href = "index.php"; 
        }
    });
});


const addModal = document.getElementById('addModal');
const editModal = document.getElementById('editModal');
const summonAddModalBtn = document.getElementById('summonAddModal');

// Open Create Modal
if(summonAddModalBtn) {
    summonAddModalBtn.addEventListener('click', () => {
        addModal.style.display = 'flex';
    });
}

// Open Edit Modal and auto-populate values from data metrics
document.querySelectorAll('.edit-trigger').forEach(button => {
    button.addEventListener('click', function() {
        const rawId = this.getAttribute('data-id');
        const numericId = parseAttractionId(rawId);
        
        document.getElementById('edit_id').value = numericId;
        document.getElementById('edit_name').value = this.getAttribute('data-name');
        document.getElementById('edit_wait').value = this.getAttribute('data-wait');
        document.getElementById('edit_status').value = this.getAttribute('data-status');
        
        editModal.style.display = 'flex';
    });
});

// Defensive verification for Banish action triggers
document.querySelectorAll('.banish-trigger').forEach(button => {
    button.addEventListener('click', function(e) {
        let targetId = this.getAttribute('data-id');
        
        // If the button is inside a form, let the form handle it (PHP will validate)
        if (this.closest('form')) {
            return;
        }
        
        // Parse RD-### format if applicable
        targetId = parseAttractionId(targetId);
        
        // If the ID is invalid (0 or NaN), block the action
        if (!targetId || targetId === 0 || isNaN(targetId)) {
            e.preventDefault();
            alert("⚠️ Critical Spell Failure: Invalid tracking metric index.");
            return;
        }
    });
});

// Close All Modals Window Hook
function closeModals() {
    if(addModal) addModal.style.display = 'none';
    if(editModal) editModal.style.display = 'none';
    const bookingModal = document.getElementById('editBookingModal');
    if(bookingModal) bookingModal.style.display = 'none';
}

// Close modals when clicking outside the boxes
window.addEventListener('click', (e) => {
    const bookingModal = document.getElementById('editBookingModal');
    if (e.target === addModal || e.target === editModal || e.target === bookingModal) {
        closeModals();
    }
});

// Bookings Triggers Setup
const summonAddBookingModalBtn = document.getElementById('summonAddBookingModal');
if(summonAddBookingModalBtn) {
    summonAddBookingModalBtn.addEventListener('click', () => { 
        const bookingModal = document.getElementById('editBookingModal');
        if(bookingModal) bookingModal.style.display = 'flex'; 
    });
}

document.querySelectorAll('.edit-booking-trigger').forEach(button => {
    button.addEventListener('click', function() {
        const bookingModal = document.getElementById('editBookingModal');
        document.getElementById('edit_booking_id').value = this.getAttribute('data-id');
        document.getElementById('edit_booking_name').value = this.getAttribute('data-name');
        document.getElementById('edit_booking_email').value = this.getAttribute('data-email');
        document.getElementById('edit_booking_status').value = this.getAttribute('data-status');
        if(bookingModal) bookingModal.style.display = 'flex';
    });
});