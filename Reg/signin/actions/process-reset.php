<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../includes/config.php";

// Debug: Check session data
error_log("DEBUG - Session data: " . print_r($_SESSION, true));
error_log("DEBUG - POST data: " . print_r($_POST, true));

// Check if user is authorized to reset password
if (!isset($_SESSION['reset_user_id'])) {
    $_SESSION['error_message'] = "Session expired. Please start over.";
    header("Location: ../pages/forgot_password.php");
    exit();
}

// Check if we're on step 2 (password reset step)
// BOTH conditions should work - either otp_verified OR reset_step == 2
$is_authorized = false;
if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
    $is_authorized = true;
} elseif (isset($_SESSION['reset_step']) && $_SESSION['reset_step'] == 2) {
    $is_authorized = true;
}

if (!$is_authorized) {
    $_SESSION['error_message'] = "Please verify OTP first.";
    header("Location: ../pages/verify_otp.php");
    exit();
}

// Initialize variables
$user_id = $_SESSION['reset_user_id'];
$error_message = '';

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in all password fields.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expires_at = NULL WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            // Success - update session
            $_SESSION['reset_step'] = 3;
            $_SESSION['success_message'] = "Password reset successfully!";
            
            // Clear reset session variables
            unset($_SESSION['reset_user_id'], $_SESSION['otp_verified'], $_SESSION['otp_expiry_time']);
            
            // Redirect to show success message
            header("Location: ../pages/verify_otp.php");
            exit();
        } else {
            $error_message = "Failed to reset password. Please try again.";
        }
        $stmt->close();
    }
    
    // If there's an error, store it in session and redirect back
    if ($error_message) {
        $_SESSION['error_message'] = $error_message;
        header("Location: ../pages/verify_otp.php");
        exit();
    }
} else {
    // Invalid access - redirect to forgot password
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: ../pages/forgot_password.php");
    exit();
}