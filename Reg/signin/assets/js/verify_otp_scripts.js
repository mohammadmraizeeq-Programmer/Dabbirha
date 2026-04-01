// OTP Timer and Inputs Handler
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the OTP verification page (step 1)
    const timerElement = document.getElementById('timer');
    const otpTimerElement = document.getElementById('otpTimer');
    const otpInputs = document.querySelectorAll('.otp-input');
    const fullOtpInput = document.getElementById('fullOtp');
    const verifyBtn = document.querySelector('button[name="verify_otp"]');
    
    // ----- OTP TIMER FUNCTIONALITY -----
    // Only run if we have timer elements AND otpExpiryTime is defined
    if (timerElement && otpTimerElement && typeof otpExpiryTime !== 'undefined' && otpExpiryTime > 0) {
        console.log('Initializing OTP timer...');
        
        // Function to update the OTP countdown timer
        function updateOtpTimer() {
            const now = Date.now();
            const remaining = otpExpiryTime - now;
            
            // Safely check if elements still exist
            if (!timerElement || !otpTimerElement) {
                console.warn('Timer elements removed from DOM');
                return;
            }
            
            if (remaining <= 0) {
                // Timer expired
                timerElement.textContent = '00:00';
                timerElement.style.color = '#dc2626';
                
                otpTimerElement.innerHTML = '<strong>OTP has expired.</strong> Please request a new one.';
                otpTimerElement.style.color = '#dc2626';
                
                if (verifyBtn) {
                    verifyBtn.disabled = true;
                    verifyBtn.style.opacity = '0.6';
                    verifyBtn.style.cursor = 'not-allowed';
                }
                
                // Disable OTP inputs
                if (otpInputs.length > 0) {
                    otpInputs.forEach(input => {
                        input.disabled = true;
                        input.style.opacity = '0.6';
                    });
                }
                
                return;
            }
            
            // Calculate minutes and seconds
            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);
            
            // Update timer display
            timerElement.textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Change color based on remaining time
            if (minutes < 1) {
                timerElement.style.color = '#dc2626'; // Red for less than 1 minute
            } else if (minutes < 3) {
                timerElement.style.color = '#f59e0b'; // Orange for less than 3 minutes
            } else {
                timerElement.style.color = '#059669'; // Green for more than 3 minutes
            }
        }
        
        // Initial timer update
        updateOtpTimer();
        
        // Update timer every second
        const timerInterval = setInterval(updateOtpTimer, 1000);
        
        // Clean up interval when leaving page
        window.addEventListener('beforeunload', function() {
            clearInterval(timerInterval);
        });
    } else {
        console.log('Timer not initialized - elements missing or invalid expiry time');
        console.log('timerElement exists:', !!timerElement);
        console.log('otpTimerElement exists:', !!otpTimerElement);
        console.log('otpExpiryTime:', otpExpiryTime);
    }
    
    // ----- OTP INPUT HANDLING -----
    if (otpInputs.length > 0) {
        console.log('Initializing OTP inputs...');
        
        // Function to update the full OTP hidden input
        function updateFullOtp() {
            let otp = '';
            otpInputs.forEach(input => {
                otp += input.value;
            });
            if (fullOtpInput) {
                fullOtpInput.value = otp;
            }
        }
        
        // Handle OTP input events
        otpInputs.forEach((input, index) => {
            // Only allow numeric input
            input.addEventListener('input', function(e) {
                // Remove non-numeric characters
                this.value = this.value.replace(/\D/g, '');
                
                // Auto-advance to next input
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                    otpInputs[index + 1].select();
                }
                
                updateFullOtp();
            });
            
            // Handle keyboard navigation
            input.addEventListener('keydown', function(e) {
                // Arrow key navigation
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                    otpInputs[index + 1].select();
                    e.preventDefault();
                }
                if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                    otpInputs[index - 1].select();
                    e.preventDefault();
                }
                
                // Handle backspace
                if (e.key === 'Backspace') {
                    if (!this.value && index > 0) {
                        // If current input is empty, focus previous and clear it
                        otpInputs[index - 1].focus();
                        otpInputs[index - 1].value = '';
                        otpInputs[index - 1].select();
                        updateFullOtp();
                    } else if (this.value) {
                        // If current input has value, clear it
                        this.value = '';
                        updateFullOtp();
                    }
                }
                
                // Handle delete
                if (e.key === 'Delete') {
                    this.value = '';
                    updateFullOtp();
                }
            });
            
            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = e.clipboardData.getData('text');
                const numbersOnly = pastedText.replace(/\D/g, '');
                
                if (numbersOnly.length === 6) {
                    // Fill all inputs with pasted OTP
                    for (let i = 0; i < 6; i++) {
                        if (otpInputs[i]) {
                            otpInputs[i].value = numbersOnly.charAt(i);
                        }
                    }
                    updateFullOtp();
                    otpInputs[5].focus(); // Focus last input
                    otpInputs[5].select();
                }
            });
            
            // Select text on click for easy editing
            input.addEventListener('click', function() {
                this.select();
            });
        });
        
        // Focus first OTP input on page load
        setTimeout(() => {
            if (otpInputs[0]) {
                otpInputs[0].focus();
                otpInputs[0].select();
            }
        }, 100);
    }
    
    // ----- PASSWORD STRENGTH INDICATOR -----
    const passwordInput = document.querySelector('input[name="new_password"]');
    if (passwordInput) {
        console.log('Initializing password strength...');
        const strengthIndicator = document.querySelector('.password-strength');
        
        passwordInput.addEventListener('input', function() {
            if (!strengthIndicator) return;
            
            const password = this.value;
            
            if (password.length === 0) {
                strengthIndicator.textContent = 'Must be at least 8 characters long';
                strengthIndicator.style.color = '#6b7280';
            } else if (password.length < 8) {
                strengthIndicator.textContent = 'Too short';
                strengthIndicator.style.color = '#dc2626';
            } else {
                // Check password strength
                let strength = 0;
                
                // Length check
                if (password.length >= 12) strength++;
                
                // Contains numbers
                if (/\d/.test(password)) strength++;
                
                // Contains lowercase
                if (/[a-z]/.test(password)) strength++;
                
                // Contains uppercase
                if (/[A-Z]/.test(password)) strength++;
                
                // Contains special characters
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                // Update display based on strength
                if (strength < 3) {
                    strengthIndicator.textContent = 'Weak';
                    strengthIndicator.style.color = '#dc2626';
                } else if (strength < 5) {
                    strengthIndicator.textContent = 'Good';
                    strengthIndicator.style.color = '#f59e0b';
                } else {
                    strengthIndicator.textContent = 'Strong';
                    strengthIndicator.style.color = '#059669';
                }
            }
        });
    }
    
    // ----- FORM VALIDATION -----
    const otpForm = document.querySelector('form[action*="process-forgot-password"]');
    if (otpForm) {
        otpForm.addEventListener('submit', function(e) {
            const otpInputs = this.querySelectorAll('.otp-input');
            let fullOtp = '';
            otpInputs.forEach(input => fullOtp += input.value);
            
            if (fullOtp.length !== 6) {
                e.preventDefault();
                alert('Please enter all 6 digits of the OTP code.');
                return false;
            }
        });
    }
    
    // Password form validation
    const passwordForm = document.querySelector('form[action*="process-reset"]');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = this.querySelector('input[name="new_password"]');
            const confirmPassword = this.querySelector('input[name="confirm_password"]');
            
            if (!newPassword || !confirmPassword) return;
            
            if (newPassword.value.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                newPassword.focus();
                return false;
            }
            
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
                confirmPassword.focus();
                return false;
            }
        });
    }
});

// Add CSS styles for better UX
const otpStyles = document.createElement('style');
otpStyles.textContent = `
    .otp-input {
        transition: all 0.2s ease;
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        letter-spacing: 2px;
    }
    
    .otp-input:focus {
        transform: scale(1.05);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        border-color: #3b82f6;
        outline: none;
    }
    
    .otp-input:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    #timer {
        font-weight: bold;
        font-family: monospace;
        font-size: 1.1rem;
    }
    
    .password-strength {
        font-size: 0.875rem;
        margin-top: 0.25rem;
        transition: color 0.3s ease;
        font-weight: 500;
    }
    
    .otp-timer {
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: 6px;
        display: inline-block;
        margin: 10px 0;
    }
`;
document.head.appendChild(otpStyles);