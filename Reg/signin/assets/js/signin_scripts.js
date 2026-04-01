// Password strength calculator
function calculatePasswordStrength(password) {
    if (password.length < 6) return 'too_short';

    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score < 2) return 'weak';
    if (score < 4) return 'medium';
    return 'strong';
}

// Toggle between Sign In and Sign Up forms
const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');
registerBtn.addEventListener('click', () => container.classList.add("active"));
loginBtn.addEventListener('click', () => container.classList.remove("active"));

// Privacy Policy Checkbox Validation
document.getElementById('signupForm').addEventListener('submit', function(e) {
    const checkbox = document.getElementById('privacyPolicyCheckbox');
    const errorDiv = document.getElementById('policyError');
    
    if (!checkbox.checked) {
        e.preventDefault(); // Stop form submission
        errorDiv.style.display = 'block';
        checkbox.focus();
        return false;
    }
    
    errorDiv.style.display = 'none';
});

// Hide error when checkbox is checked
document.getElementById('privacyPolicyCheckbox').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('policyError').style.display = 'none';
    }
});

// Password strength indicator
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const strengthElement = document.getElementById('passwordStrength');
    const strengthMeter = document.getElementById('passwordStrengthMeter');
    
    const signinPasswordInput = document.getElementById('signinPassword');
    const toggleSigninPasswordBtn = document.getElementById('toggleSigninPassword');
    
    // Function to toggle password visibility
    function setupPasswordToggle(inputElement, toggleBtn) {
        if (toggleBtn && inputElement) {
            toggleBtn.addEventListener('click', function() {
                const type = inputElement.getAttribute('type') === 'password' ? 'text' : 'password';
                inputElement.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.className = type === 'password' ? 'ri-eye-line' : 'ri-eye-off-line';
            });
        }
    }
    
    // Setup password toggles
    setupPasswordToggle(passwordInput, togglePasswordBtn);
    setupPasswordToggle(signinPasswordInput, toggleSigninPasswordBtn);
    
    // Password strength checking for signup
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            if (strengthElement && strengthMeter) {
                const meterFill = strengthMeter.querySelector('.password-strength-meter-fill');
                
                if (password.length === 0) {
                    strengthElement.textContent = '';
                    strengthElement.className = 'password-strength-text';
                    strengthMeter.className = 'password-strength-meter';
                    meterFill.style.width = '0';
                } else if (password.length < 6) {
                 strengthElement.textContent = 'Password must be at least 6 characters.';
                    strengthElement.className = 'password-strength-text text-danger';
                    strengthMeter.className = 'password-strength-meter weak';
                } else if (strength === 'weak') {
                    strengthElement.textContent = 'Password strength: Weak';
                    strengthElement.className = 'password-strength-text text-danger';
                    strengthMeter.className = 'password-strength-meter weak';
                } else if (strength === 'medium') {
                    strengthElement.textContent = 'Password strength: Medium';
                    strengthElement.className = 'password-strength-text text-warning';
                    strengthMeter.className = 'password-strength-meter medium';
                } else {
                    strengthElement.textContent = 'Password strength: Strong';
                    strengthElement.className = 'password-strength-text text-success';
                    strengthMeter.className = 'password-strength-meter strong';
                }
            }
        });
    }
    
    // Form validation for password strength
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const strength = calculatePasswordStrength(password);
            
            if (strength === 'too_short') {
                e.preventDefault();
                if (strengthElement) {
                    strengthElement.textContent = 'Password must be at least 6 characters long';
                    strengthElement.className = 'password-strength-text text-danger';
                }
                passwordInput.focus();
                return false;
            }
            
            // Optional: Warn for weak passwords
            if (strength === 'weak') {
                const confirmWeak = confirm('Your password is weak. Are you sure you want to continue?');
                if (!confirmWeak) {
                    e.preventDefault();
                    passwordInput.focus();
                    return false;
                }
            }
        });
    }
});

// Google Sign-In
const CLIENT_ID = "84776159395-u2bg538ej9gb816uuvvsesc9r12o82jh.apps.googleusercontent.com";

function handleGoogleResponse(response) {
    fetch("../actions/google-auth.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token: response.credential })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Normalize the redirect path check (fixing the 'signin' typo)
            const targetPath = "/Dabbirha/Reg/signin/pages/verify_notice.php";
            
            if (data.redirect.includes("verify_notice.php")) {
                let finalUrl = targetPath;
                
                // Append email if it exists
                if (data.email) {
                    finalUrl += "?email=" + encodeURIComponent(data.email);
                }
                window.location.href = finalUrl;
            } else {
                // For dashboard/admin, use the path sent by PHP
                window.location.href = data.redirect;
            }
        } else {
            alert("Google sign-in failed: " + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Something went wrong. Please try again.");
    });
}

window.onload = function () {
    google.accounts.id.initialize({
        client_id: CLIENT_ID,
        callback: handleGoogleResponse
    });

    // Render buttons
    google.accounts.id.renderButton(
        document.getElementById("googleSignUpButton"),
        { theme: "outline", size: "large", text: "signup_with" }
    );
    google.accounts.id.renderButton(
        document.getElementById("googleSignInButton"),
        { theme: "outline", size: "large", text: "signin_with" }
    );
};

// Combine country code and phone number before form submission
function combinePhoneNumber() {
    const countryCode = document.getElementById('countryCode').value; // +962
    const phonePartial = document.getElementById('signupPhone').value.trim(); // 7XXXXXXXX
    
    // Combine them (remove any leading + from partial if exists)
    const cleanPartial = phonePartial.replace(/^\+/, '');
    const fullPhone = countryCode + cleanPartial;
    
    // Set the value to hidden input
    document.getElementById('fullPhone').value = fullPhone;
    
    return fullPhone;
}

// Add validation for phone number format
function validatePhoneNumber(phone) {
    // Phone format: +9627XXXXXXXX (Jordan mobile numbers start with 7)
    const phoneRegex = /^\+9627\d{8}$/;
    return phoneRegex.test(phone);
}

// Update form submission to include phone validation
document.getElementById('signupForm').addEventListener('submit', function(e) {
    // ... existing privacy policy validation ...
    
    // Combine and validate phone number
    const fullPhone = combinePhoneNumber();
    
    if (!validatePhoneNumber(fullPhone)) {
        e.preventDefault();
        alert('Please enter a valid Jordanian phone number (e.g., 7XXXXXXXX)');
        document.getElementById('signupPhone').focus();
        return false;
    }
    
    // ... rest of existing validation ...
});

// Handle URL error parameters with SweetAlert2 and clean the URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        
        // 1. Immediately remove the error from the URL bar without refreshing
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);

        // 2. Trigger the appropriate SweetAlert with heightAuto disabled
        if (errorType === 'invalid') {
            Swal.fire({
                title: 'Incorrect Password',
                text: 'The password you entered is incorrect. Please try again.',
                icon: 'error',
                confirmButtonColor: '#1e293b',
                heightAuto: false // Prevents the container from jumping up
            });
        } else if (errorType === 'notfound') {
            Swal.fire({
                title: 'Account Not Found',
                text: 'No user found with that email or name.',
                icon: 'warning',
                confirmButtonColor: '#1e293b',
                heightAuto: false
            });
        } else if (errorType === 'unverified') {
            Swal.fire({
                title: 'Verify Your Email',
                text: 'Please check your email to verify your account before signing in.',
                icon: 'info',
                confirmButtonColor: '#1e293b',
                heightAuto: false
            });
        } else if (errorType === 'empty') {
            Swal.fire({
                title: 'Fields Required',
                text: 'Please enter both your email and password.',
                icon: 'question',
                confirmButtonColor: '#1e293b',
                heightAuto: false
            });
        }
    }
});