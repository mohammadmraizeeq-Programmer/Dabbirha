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
    <link rel="stylesheet" href="../assets/css/verify_style.css">
   
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
                <a href="../../signin/pages/signin.php" class="btn btn-primary" id="signinBtn">
                    <i class="ri-login-box-line"></i>
                    Sign In Now
                </a>
            <?php else: ?>
                <a href="../../signin/pages/signin.php" class="btn btn-primary">
                    <i class="ri-login-box-line"></i>
                    Sign In
                </a>
                <a href="signup.php" class="btn btn-secondary">
                    <i class="ri-user-add-line"></i>
                    Sign Up
                </a>
            <?php endif; ?>
        </div>
        
        <?php if($type === 'success'): ?>
            <div class="auto-redirect">
                <p>You will be automatically redirected to sign in page in <span id="countdown">10</span> seconds...</p>
            </div>
            
            <script>
                // Auto-redirect countdown to signin.php
                let seconds = 10;
                const countdownElement = document.getElementById('countdown');
                
                const countdown = setInterval(function() {
                    seconds--;
                    countdownElement.textContent = seconds;
                    
                    if (seconds <= 0) {
                        clearInterval(countdown);
                        window.location.href = '../../signin/pages/signin.php';
                    }
                }, 1000);
                
                // Also redirect on button click
                const signinBtn = document.getElementById('signinBtn');
                if (signinBtn) {
                    signinBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearInterval(countdown);
                        
                        // Show loading state
                        this.innerHTML = '<div class="loader"></div> Redirecting...';
                        this.disabled = true;
                        
                        // Redirect after short delay
                        setTimeout(() => {
                            window.location.href = '../../signin/pages/signin.php';
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
    


<script src="../assets/js/verify_scripts.js"></script>
</body>
</html>