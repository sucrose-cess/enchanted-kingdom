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
const summonAddCustomerModalBtn = document.getElementById('summonAddCustomerModal');
const addCustomerModal = document.getElementById('addCustomerModal');
const editCustomerModal = document.getElementById('editCustomerModal');

// Open Create Modal
if(summonAddModalBtn) {
    summonAddModalBtn.addEventListener('click', () => {
        addModal.style.display = 'flex';
    });
}

// Open Add Customer Modal
if (summonAddCustomerModalBtn) {
    summonAddCustomerModalBtn.addEventListener('click', () => {
        if (addCustomerModal) addCustomerModal.style.display = 'flex';
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
    if(addCustomerModal) addCustomerModal.style.display = 'none';
    if(editCustomerModal) editCustomerModal.style.display = 'none';
}

// Close modals when clicking outside the boxes
window.addEventListener('click', (e) => {
    if (e.target === addModal || e.target === editModal || e.target === addCustomerModal || e.target === editCustomerModal) {
        closeModals();
    }
});

// Load bookings for a selected customer when their row is clicked
document.querySelectorAll('.customer-row').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', function() {
        const cid = this.getAttribute('data-customer-id');
        const container = document.getElementById('customerBookingsContainer');
        if (!cid || !container) return;
        container.innerHTML = '<div class="glass-panel" style="padding:20px;">Loading bookings...</div>';
        fetch('customer_bookings.php?customer_id=' + encodeURIComponent(cid))
            .then(r => r.text())
            .then(html => { container.innerHTML = html; })
            .catch(err => { container.innerHTML = '<div class="glass-panel" style="padding:20px;">Error loading bookings.</div>'; console.error(err); });
    });
});

// Open Edit Customer modal
document.querySelectorAll('.edit-customer-trigger').forEach(btn => {
    btn.addEventListener('click', function() {
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
        if (editCustomerModal) editCustomerModal.style.display = 'flex';
    });
});