<?php
session_start();
$step = $_SESSION['reset_step'] ?? 1;
$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;
$otp_expired = $_SESSION['otp_expired'] ?? false;
$otp_error = $_SESSION['otp_error'] ?? null;

// Calculate remaining time for OTP
$otp_expiry_time = $_SESSION['otp_expiry_time'] ?? 0;
$remaining_time = max(0, $otp_expiry_time - time());

// Clear messages after showing
unset($_SESSION['error_message'], $_SESSION['success_message'], $_SESSION['otp_error'], $_SESSION['otp_expired']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Dabbirha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/verify_otp_style.css?v=3">
</head>
<body>
    <div class="card">
        <?php if ($step === 3): ?>
            <!-- Success Message -->
            <div class="success-card">
                <div class="status-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h1>Password Reset Successful</h1>
                <p class="description">
                    Your password has been reset successfully. You can now sign in with your new password.
                </p>
                <div class="actions">
                    <a href="signin.php" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?php echo $step == 1 ? 'active' : ''; ?>"></div>
                <div class="step <?php echo $step == 2 ? 'active' : ''; ?>"></div>
            </div>
            
            <div class="status-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            
            <h1><?php echo $step == 1 ? 'Verify OTP' : 'Create New Password'; ?></h1>
            
            <?php if ($error_message): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <!-- Step 1: OTP Verification -->
                <p class="description">
                    Enter the 6-digit verification code sent to your phone number.
                </p>
                
                <?php if ($otp_expired): ?>
                    <div class="message error">
                        OTP has expired. Please request a new one.
                    </div>
                <?php else: ?>
                    <div class="otp-timer" id="otpTimer">
                        Code expires in: <span id="timer"><?php echo gmdate("i:s", $remaining_time); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($otp_error): ?>
                    <div class="message error"><?php echo htmlspecialchars($otp_error); ?></div>
                <?php endif; ?>
                
                <form action="../actions/process-forgot-password.php" method="POST" autocomplete="off">
                    <input type="hidden" name="action" value="verify_otp">
                    <div class="otp-container">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <input type="text" 
                                   name="otp_<?php echo $i; ?>" 
                                   class="otp-input" 
                                   maxlength="1" 
                                   data-index="<?php echo $i; ?>"
                                   <?php echo $otp_expired ? 'disabled' : ''; ?>>
                        <?php endfor; ?>
                        <input type="hidden" name="otp" id="fullOtp">
                    </div>
                    
                    <button type="submit" 
                            name="verify_otp" 
                            class="btn btn-primary"
                            <?php echo $otp_expired ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : ''; ?>>
                        <i class="bi bi-check-circle"></i>
                        Verify Code
                    </button>
                </form>
                
                <!-- Separate form for resend -->
                <form action="../actions/process-forgot-password.php" method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="action" value="resend_otp">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['reset_user_id'] ?? ''; ?>">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                        <?php echo $otp_expired ? 'Resend OTP' : 'Resend Code'; ?>
                    </button>
                </form>
                
                <div class="back-link">
                    <a href="signin.php">
                        <i class="bi bi-arrow-left"></i>
                        Back to signin
                    </a>
                </div>
                
            <?php elseif ($step == 2): ?>
                <!-- Step 2: Password Reset -->
                <p class="description">
                    Create a strong password for your account.
                </p>
                
                <form action="../actions/process-reset.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" 
                               name="new_password" 
                               class="form-input" 
                               placeholder="Enter new password" 
                               required
                               minlength="8">
                        <div class="password-strength">
                            Must be at least 8 characters long
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" 
                               name="confirm_password" 
                               class="form-input" 
                               placeholder="Confirm new password" 
                               required>
                    </div>
                    
                    <button type="submit" name="reset_password" class="btn btn-primary">
                        <i class="bi bi-key"></i>
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
    // Pass PHP data to JavaScript
    const otpExpiryTime = <?php echo (time() + $remaining_time) * 1000; ?>; // Convert to milliseconds
    const remainingTime = <?php echo $remaining_time; ?>; // In seconds
    </script>
    <script src="../assets/js/verify_otp_scripts.js?v=2"></script>
</body>
</html>