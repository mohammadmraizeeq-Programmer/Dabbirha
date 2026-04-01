<?php
session_start();

// Check if user is coming from Google signup
if (isset($_SESSION['google_signup_email'])) {
    $email = $_SESSION['google_signup_email'];
    $name = $_SESSION['google_signup_name'] ?? '';
    
    // Clear the session data
    unset($_SESSION['google_signup_email']);
    unset($_SESSION['google_signup_name']);
    
    // Set flag to show this is a new user
    $new_user = true;
} else {
    // Check if user is coming from regular signup
    $email = $_GET['email'] ?? '';
    $new_user = isset($_SESSION['new_user']) ? $_SESSION['new_user'] : false;
}

// If no email provided and not a new user, redirect to home
if (empty($email) && !$new_user) {
    header("Location: /Dabbirha/index.php");
    exit;
}

// Clear the session flag if it exists
unset($_SESSION['new_user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email | Dabbirha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/verify_notice_style.css?V=2">
   
</head>
<body>
    <div class="verification-card">
        <div class="email-icon">
            <i class="ri-mail-send-line"></i>
        </div>
        
        <h1>Check Your Email</h1>
        
        <p class="lead">
            We've sent a verification link to your email address.
        </p>
        
        <?php if(!empty($email)): ?>
            <div class="email-address">
                <i class="ri-mail-line"></i>
                <?php echo htmlspecialchars($email); ?>
            </div>
        <?php endif; ?>
        
        <div class="alert-info">
            <i class="ri-information-line"></i>
            <strong>Important:</strong> Please check your spam or junk folder if you don't see the email in your inbox.
        </div>
        
        <div class="resend-section"  data-email="<?php echo htmlspecialchars($email); ?>">
            <button class="resend-btn" id="resendBtn">
                <i class="ri-refresh-line"></i>
                Resend Verification Email
            </button>
            <div class="timer" id="timer">
                You can request a new email in <span id="countdown">60</span> seconds
            </div>
        </div>
        
        <div class="actions">
        <a href="/Dabbirha/index.php" class="btn btn-secondary">
                <i class="ri-home-line"></i>
                Back to Home
            </a>

            <a href="../../signin/pages/signin.php" class="btn btn-primary">
                <i class="ri-login-box-line"></i>
                Go to Sign In
            </a>
         
        </div>
        
        <div class="mt-4 pt-4 border-top text-muted small">
            <p><i class="ri-question-line me-1"></i> Need help? <a href="/Dabbirha/contact.php" class="text-decoration-none">Contact Support</a></p>
        </div>
    </div>
    
  
    <script src="../assets/js/verify_notice_scripts.js?v=<?php echo time();?>"></script>
</body>
</html>