AOS.init({
    duration: 800,
    once: true
});

// Simple copy functionality
document.addEventListener('DOMContentLoaded', function() {
    // Phone number click to copy
    const phoneNumber = document.querySelector('.phone-number');
    if (phoneNumber) {
        phoneNumber.addEventListener('click', function(e) {
            e.preventDefault();
            const text = this.textContent.trim();
            navigator.clipboard.writeText(text.replace(/\s/g, '')).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="ri-check-line me-1"></i>Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    }
    
    // Email click to copy
    const emailAddress = document.querySelector('.email-address');
    if (emailAddress) {
        emailAddress.addEventListener('click', function(e) {
            e.preventDefault();
            const text = this.textContent.trim();
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="ri-check-line me-1"></i>Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    }
    
    // Emergency number click to copy
    const emergencyNumber = document.querySelector('.emergency-number');
    if (emergencyNumber) {
        emergencyNumber.addEventListener('click', function(e) {
            e.preventDefault();
            const text = this.textContent.trim().replace('+962 ', '+962');
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="ri-check-line me-1"></i>Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    }
});