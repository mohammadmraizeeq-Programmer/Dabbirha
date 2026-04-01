// ----------------------------
// VARIABLES
// ----------------------------
let canResend = false;
let countdown = 60;
let countdownInterval = null;

// Use DOMContentLoaded to ensure elements are found correctly
document.addEventListener('DOMContentLoaded', () => {
    const resendBtn = document.getElementById('resendBtn');
    const timerElement = document.getElementById('timer');
    const resendSection = document.querySelector('.resend-section');

    // ----------------------------
    // START COUNTDOWN FUNCTION
    // ----------------------------
    function startCountdown() {
        canResend = false;
        countdown = 60;

        resendBtn.disabled = true;
        resendBtn.style.opacity = '0.7';
        resendBtn.innerHTML = 'Resend available in <span id="countdown">60</span>s';
        timerElement.innerHTML = 'Please wait before resending';

        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        countdownInterval = setInterval(() => {
            countdown--;
            const countdownSpan = document.getElementById('countdown');
            if (countdownSpan) countdownSpan.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                canResend = true;
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
                resendBtn.innerHTML = '<i class="ri-refresh-line"></i> Resend Verification Email';
                timerElement.innerHTML = 'You can now request a new verification email';
            }
        }, 1000);
    }

    // ----------------------------
    // RESEND BUTTON CLICK HANDLER
    // ----------------------------
    async function handleResendClick() {
        if (!canResend) return;
    
        // 1. Debug: Check if the button click is even registered
        console.log("Resend button clicked!");
    
        const email = resendSection.getAttribute('data-email');
        
        // 2. Debug: Check what email the JS is picking up
        console.log("Email detected from HTML:", email);
    
        if (!email) {
            console.error("Error: No email found in data-email attribute!");
            showMessage('error', 'Email address not found.');
            return;
        }
    
        const targetUrl = '/Dabbirha/Reg/signin/actions/resend-verfication.php';
        console.log("Attempting fetch to:", targetUrl);
    
        try {
            const response = await fetch(targetUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, action: 'resend_verification' })
            });
    
            // 3. Debug: Check the raw server response
            console.log("Server Response Status:", response.status);
            
            const result = await response.json();
            console.log("Server JSON Result:", result);
            if (result.success) {
                // Use the message from PHP, or a default string if it's missing
                showMessage('success', result.message || 'Verification email sent!');
                startCountdown(); 
            } else {
                showMessage('error', result.error);
            }
        } catch (error) {
            console.error('Network/Parsing Error:', error);
            showMessage('error', 'Network error occurred.');
        }
    }

    function showMessage(type, message) {
        document.querySelectorAll('.alert-message').forEach(el => el.remove());
        const div = document.createElement('div');
        div.className = `alert-message mt-3 ${type === 'success' ? 'alert-info' : 'alert-danger'}`;
        div.innerHTML = type === 'success'
            ? `<i class="ri-check-line"></i> <strong>Success!</strong> ${message}`
            : `<i class="ri-error-warning-line"></i> <strong>Error!</strong> ${message}`;
        resendSection.appendChild(div);
        setTimeout(() => div.remove(), 5000);
    }

    function resetButton(html) {
        resendBtn.innerHTML = html;
        resendBtn.disabled = false;
        resendBtn.style.opacity = '1';
    }

    // ----------------------------
    // INITIALIZE
    // ----------------------------
    // Attach the click event listener
    resendBtn.addEventListener('click', handleResendClick);
    
    // Start initial cooldown
    startCountdown();
});

// ----------------------------
// STYLES (Keep outside DOMContentLoaded)
// ----------------------------
const style = document.createElement('style');
style.textContent = `
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .alert-message { padding: 12px 16px; border-radius: 8px; margin-top: 15px; animation: fadeIn 0.3s ease; }
    .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
`;
document.head.appendChild(style);