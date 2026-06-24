// 1. Dynamic Page Behavior: Sticky Glowing Navbar on Scroll
const navbar = document.getElementById('navbar');

window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// 2. Form Validation (Client-Side) - FIXED IDS HERE
const inquiryForm = document.getElementById('bookingForm'); // Fixed from 'inquiryForm'
const nameInput = document.getElementById('traveler_name'); // Fixed from 'name'
const emailInput = document.getElementById('traveler_email'); // Fixed from 'email'
const ticketTypeEl = document.getElementById('ticket_type');
const visitDateEl = document.getElementById('visit_date');
const adultCountEl = document.getElementById('adult_count');
const childrenCountEl = document.getElementById('children_count');
const seniorCountEl = document.getElementById('senior_pwd_count');
const totalAmountEl = document.getElementById('total_amount_php');
const partyErrorEl = document.getElementById('party-error-msg');

if (inquiryForm) {
    inquiryForm.addEventListener('submit', function(event) {
        event.preventDefault(); 
        let isValid = true;

        // Validate Name
        if (!nameInput || nameInput.value.trim() === '') {
            showError(nameInput, 'Please provide the traveler\'s name.');
            isValid = false;
        } else {
            clearError(nameInput);
        }

        // Validate Email
        if (!emailInput || emailInput.value.trim() === '') {
            showError(emailInput, 'Please provide a celestial mail address.');
            isValid = false;
        } else if (!isValidEmail(emailInput.value)) {
            showError(emailInput, 'The spell failed. Please enter a valid email.');
            isValid = false;
        } else {
            clearError(emailInput);
        }

        if (isValid) {
            // Compute total based on selected pass and party size
            try {
                const prices = {
                    'Regular Day Pass': 1200,
                    'Junior Pass': 800,
                    'VIP Magic Pass': 2500
                };
                const ticket = ticketTypeEl ? ticketTypeEl.value : 'Regular Day Pass';
                const price = prices[ticket] || 1200;
                const adults = parseInt(adultCountEl?.value || 0, 10);
                const children = parseInt(childrenCountEl?.value || 0, 10);
                const seniors = parseInt(seniorCountEl?.value || 0, 10);
                const partyTotal = adults + children + seniors;
                if (partyTotal <= 0) {
                    if (partyErrorEl) partyErrorEl.innerText = 'Please select at least one guest.';
                    isValid = false;
                } else {
                    if (partyErrorEl) partyErrorEl.innerText = '';
                }

                const total = partyTotal * price;
                if (totalAmountEl) totalAmountEl.value = total;
            } catch (e) {
                console.warn('Error computing total:', e);
            }

            if (isValid) inquiryForm.submit();
        }
    });
}

function showError(inputElement, message) {
    const formGroup = inputElement.parentElement;
    const errorMsg = formGroup.querySelector('.error-msg');
    errorMsg.innerText = message;
    errorMsg.style.display = 'block';
    inputElement.style.borderColor = '#ff5252';
}

function clearError(inputElement) {
    const formGroup = inputElement.parentElement;
    const errorMsg = formGroup.querySelector('.error-msg');
    errorMsg.style.display = 'none';
    inputElement.style.borderColor = 'rgba(255, 255, 255, 0.2)';
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// 3. Interactive Web Event Magic: Click Sparkles
document.addEventListener('click', function(e) {
    const sparkle = document.createElement('div');
    sparkle.innerHTML = '✨';
    sparkle.style.position = 'absolute';
    sparkle.style.left = `${e.pageX - 10}px`;
    sparkle.style.top = `${e.pageY - 10}px`;
    sparkle.style.color = '#ffd54f';
    sparkle.style.fontSize = '20px';
    sparkle.style.pointerEvents = 'none';
    sparkle.style.zIndex = '9999';
    document.body.appendChild(sparkle);
    
    setTimeout(() => {
        sparkle.remove();
    }, 1000);
});

// 4. Magic Mouse Glitter Trail
document.addEventListener('mousemove', function(e) {
    const glitter = document.createElement('div');
    glitter.classList.add('magic-glitter');
    
    glitter.style.left = e.clientX + 'px';
    glitter.style.top = e.clientY + 'px';
    
    const size = Math.random() * 6 + 4; 
    glitter.style.width = size + 'px';
    glitter.style.height = size + 'px';

    document.body.appendChild(glitter);
    
    setTimeout(() => {
        glitter.remove();
    }, 800);
});