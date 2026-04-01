<?php
session_start();
$type = $_SESSION['verify_type'] ?? 'error';
$message = $_SESSION['verify_message'] ?? 'Access denied.';


// Clear session variables after use to prevent showing same message on refresh
unset($_SESSION['verify_type'], $_SESSION['verify_message']);

// Important: Note that styles should ideally be in ../assets/css3/registration.css
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | Dabbirha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/verify_2_style.css?v=2">
    
</head>
<body>
    <div class="verification-card <?php echo $type; ?>">
        <div class="status-icon">
            <?php 
            if ($type === 'success') {
                echo '<i class="ri-checkbox-circle-fill"></i>';
            } elseif ($type === 'error') {
                echo '<i class="ri-close-circle-fill"></i>';
            } else {
                echo '<i class="ri-information-fill"></i>';
            }
            ?>
        </div>
        
        <h1>
            <?php 
            if ($type === 'success') {
                echo 'Email Verified Successfully!';
            } elseif ($type === 'error') {
                echo 'Verification Failed';
            } else {
                echo 'Already Verified';
            }
            ?>
        </h1>
        
        <p class="description">
            <?php echo htmlspecialchars($message); ?>
        </p>
        
        <div class="actions">
            <?php if($type === 'success'): ?>
                <a href="/Dabbirha/index.php" class="btn btn-secondary">
                    <i class="ri-home-line"></i>
                    Go Home
                </a>
                <a href="/Dabbirha/Reg/signup_provider/pages/provider-registration.php?sstep=5&email" class="btn btn-primary" id="continueBtn">
    <i class="ri-arrow-right-line"></i>
    Complete Registration
</a>

      
            <?php else: ?>
                <a href="../../signin/pages/signin.php" class="btn btn-primary">
                    <i class="ri-login-box-line"></i>
                    Sign In
                </a>
                <a href="/Dabbirha/index.php" class="btn btn-secondary">
                    <i class="ri-home-line"></i>
                    Home
                </a>
            <?php endif; ?>
        </div>
        
        <?php if($type === 'success'): ?>
            <div class="auto-redirect">
                <p>You will be automatically redirected to the next step in <span id="countdown">10</span> seconds...</p>
            </div>
            
            <script>
                 // Auto-redirect countdown to step 4
                 let seconds = 10;
                const countdownElement = document.getElementById('countdown');
                
                const countdown = setInterval(function() {
                    seconds--;
                    countdownElement.textContent = seconds;
                    
                    if (seconds <= 0) {
                        clearInterval(countdown);
                        // UPDATED: Redirect to step 5
                        window.location.href = '/Dabbirha/Reg/signup_provider/pages/provider-registration.php?step=5';
                    }
                }, 1000);
                
                // Also redirect on button click
                const continueBtn = document.getElementById('continueBtn');
                if (continueBtn) {
                    continueBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearInterval(countdown);
                        
                        // Show loading state
                        this.innerHTML = '<div class="loader"></div> Redirecting...';
                        this.disabled = true;
                        
                        // Redirect after short delay
                        setTimeout(() => {
                            // UPDATED: Redirect to step 5
                            window.location.href = '/Dabbirha/Reg/signup_provider/pages/provider-registration.php?step=5';
                        }, 500);
                    });
                }
            </script>
        <?php endif; ?>
        
        <?php if($type === 'error'): ?>
            <div class="mt-4 pt-4 border-top">
                <p class="text-muted small mb-2">Need help?</p>
                <a href="/Dabbirha/contact.php" class="text-decoration-none small">Contact Support</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../assets/js/verify_2_scripts.js"></script>
</body>
</html>