<?php
session_start();
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Clear messages after reading
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Dabbirha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/forget_password_style.css?v=2">
</head>
<body>
    <div class="forgot-container">
        <div class="logo">
            <div class="icon-container">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h2>Reset Password</h2>
            <p>Enter your email or phone to receive a verification code</p>
        </div>

        <?php if(isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <?php if(isset($_SESSION['user_email'])): ?>
                    <div class="mt-2">
                        <small>If you don't receive the code, check your registered phone number or contact support.</small>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(isset($_SESSION['reset_user_id'])): ?>
                <div class="text-center">
                    <a href="verify_otp.php" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>
                        Continue to Verification
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(!isset($success_message)): ?>
            <form method="POST" action="../actions/process-forgot-password.php" id="resetForm">
                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <p><i class="bi bi-info-circle"></i> We'll send a 6-digit verification code to your registered phone number.</p>
                </div>

                <div class="mb-3">
                    <label for="login_input" class="form-label">Email or Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="login_input" 
                               name="login_input" 
                               placeholder="Enter your email or phone number"
                               required
                               autocomplete="off"
                               value="<?php echo isset($_POST['login_input']) ? htmlspecialchars($_POST['login_input']) : ''; ?>">
                    </div>
                    <div class="form-text">Enter the email or phone number associated with your Dabbirha account.</div>
                </div>

                <button type="submit" 
                        name="request_otp" 
                        class="btn btn-primary w-100"
                        id="submitBtn">
                    <span id="buttonText">Send Verification Code</span>
                    <span class="loading spinner-border spinner-border-sm" id="spinner"></span>
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="signin.php">
                <i class="bi bi-arrow-left"></i>
                Back to signin
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
 

     
    </script>
</body>
</html>