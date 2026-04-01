<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Sign Up</title>

   
    <!-- SweetAlert2 Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/signin_style.css?v=2">

    <script src="https://accounts.google.com/gsi/client" async defer></script>
 
</head>
<body>


<div class="container" id="container">

<!-- SIGN UP FORM -->
<div class="form-container sign-up">
    <form method="POST" action="../actions/process-signin.php" id="signupForm">
   

        <!-- Google Button -->
        <div id="googleSignUpButton" style="margin:20px 0;"></div>

        <span>or use your email for registration</span>

        <input type="text" name="name" id="signupName" placeholder="Full Name" required>
        <input type="email" name="email" id="signupEmail" placeholder="Email" required>
       
   <!-- Fixed +962 -->
<div style="display: flex; align-items: center;">
    <!-- Fixed +962 -->
    <input type="text" id="countryCode" value="+962" readonly style="width: 60px; text-align: center; border-right: none; background-color: #eee;">
    
    <!-- Editable rest of the number -->
    <input 
        type="tel" 
        id="signupPhone" 
        name="phone_partial" 
        placeholder="7XXXXXXXX" 
        required 
        style="flex: 1; border-left: none; padding-left: 8px;"
    >
    <!-- Hidden input to store full phone number -->
    <input type="hidden" id="fullPhone" name="phone">
</div>

        
        <!-- Password Field with Toggle and Strength Meter -->
        <div class="password-input-wrapper">
        <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="button" class="toggle-password-btn" id="togglePassword">
                <i class="ri-eye-line"></i>
            </button>
        </div>
        <div id="passwordStrength" class="password-strength-text"></div>
        <div id="passwordStrengthMeter" class="password-strength-meter">
            <div class="password-strength-meter-fill"></div>
        </div>
        
        <!-- Privacy Policy Checkbox -->
        <div class="policy-checkbox">
            <input type="checkbox" name="privacy_policy" id="privacyPolicyCheckbox" required>
            <label>
    I agree to the
    <a href="policy.php" target="_blank">
        Privacy Policy & Terms of Service
    </a>
</label>
        </div>
        <div id="policyError" class="policy-error">
    You must accept the Terms and Privacy Policy
</div>

<button type="submit" name="signup" id="signupSubmit">Sign Up</button>
    </form>
</div>

<!-- SIGN IN FORM -->
<div class="form-container sign-in">
    <form method="POST" action="../actions/process-signin.php">
    <h1>Sign In</h1>

        <!-- Google Button -->
        <div id="googleSignInButton" style="margin:20px 0;"></div>

        <span>or use your email for login</span>



        <input type="text" name="login" id="signinEmail" placeholder="Email or Name" required>
        

        
        <!-- Password Field for Sign In -->
        <div class="password-input-wrapper">
        <input type="password" name="password" id="signinPassword" placeholder="Password" required>
            <button type="button" class="toggle-password-btn" id="toggleSigninPassword">
                <i class="ri-eye-line"></i>
            </button>
        </div>
        <a href="forgot_password.php" id="forgotPasswordLink">Forgot Your Password?</a>

<button type="submit" name="signin" id="signinSubmit">Sign In</button>
     
        <a href="/Dabbirha/index.php" id="backHomeLink">
            Back to Home
        </a>
    </form>
</div>

<!-- TOGGLE PANELS -->
<div class="toggle-container">
    <div class="toggle">
        <div class="toggle-panel toggle-left">
        <h1>Welcome Back!</h1>
        <p>Enter your personal details to use all site features</p>
        <button class="hidden" id="login">Sign In</button>
        </div>
        <div class="toggle-panel toggle-right">
        <h1>Hello, Friend!</h1>
        <p>Register to access all site features</p>
        <button class="hidden" id="register">Sign Up</button>
        </div>
    </div>
</div>

</div>

<script src="../assets/js/signin_scripts.js?v=2"></script>


</body>
</html>