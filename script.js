// 1. Dynamic Page Behavior: Sticky Glowing Navbar on Scroll
const navbar = document.getElementById('navbar');

window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// 2. Form Validation (Client-Side)
const inquiryForm = document.getElementById('inquiryForm');
const nameInput = document.getElementById('name');
const emailInput = document.getElementById('email');

if (inquiryForm) {
    inquiryForm.addEventListener('submit', function(event) {
        event.preventDefault(); 
        let isValid = true;

        // Validate Name
        if (nameInput.value.trim() === '') {
            showError(nameInput, 'Please provide the traveler\'s name.');
            isValid = false;
        } else {
            clearError(nameInput);
        }

        // Validate Email
        if (emailInput.value.trim() === '') {
            showError(emailInput, 'Please provide a celestial mail address.');
            isValid = false;
        } else if (!isValidEmail(emailInput.value)) {
            showError(emailInput, 'The spell failed. Please enter a valid email.');
            isValid = false;
        } else {
            clearError(emailInput);
        }

        if (isValid) {
            inquiryForm.submit(); 
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
    // Use pageX and pageY to account for scrolling
    sparkle.style.left = `${e.pageX - 10}px`;
    sparkle.style.top = `${e.pageY - 10}px`;
    sparkle.style.color = '#ffd54f';
    sparkle.style.fontSize = '20px';
    sparkle.style.pointerEvents = 'none';
    sparkle.style.zIndex = '9999';
    document.body.appendChild(sparkle);
    
    // Remove the sparkle after 1 second so they don't pile up
    setTimeout(() => {
        sparkle.remove();
    }, 1000);
});

// 4. Magic Mouse Glitter Trail (NEW!)
document.addEventListener('mousemove', function(e) {
    // 1. Create a new div element for the sparkle
    const glitter = document.createElement('div');
    glitter.classList.add('magic-glitter');
    
    // 2. Position it exactly where the mouse pointer is (clientX/Y works perfectly with position: fixed in CSS)
    glitter.style.left = e.clientX + 'px';
    glitter.style.top = e.clientY + 'px';
    
    // 3. Give it a slightly random size (between 4px and 10px) so it looks natural
    const size = Math.random() * 6 + 4; 
    glitter.style.width = size + 'px';
    glitter.style.height = size + 'px';

    // 4. Add the sparkle to the web page
    document.body.appendChild(glitter);
    
    // 5. Remove the sparkle after 800 milliseconds (when the CSS animation finishes)
    setTimeout(() => {
        glitter.remove();
    }, 800);
});