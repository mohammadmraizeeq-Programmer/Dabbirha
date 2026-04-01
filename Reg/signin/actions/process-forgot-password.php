<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../includes/config.php";
require '../../../vendor/autoload.php';

use Twilio\Rest\Client;
$TWILIO_SID = TWILIO_SID;
$TWILIO_TOKEN = TWILIO_TOKEN;
$TWILIO_FROM_NUMBER = TWILIO_FROM_NUMBER;
$action = $_POST['action'] ?? '';

if ($action === 'verify_otp') {
    // OTP Verification
    $otp_digits = '';
    for ($i = 1; $i <= 6; $i++) {
        $otp_digits .= $_POST["otp_$i"] ?? '';
    }
    
    $otp = trim($otp_digits);
    
    if (empty($otp) || strlen($otp) !== 6) {
        $_SESSION['error_message'] = "Please enter a valid 6-digit OTP.";
        header("Location: ../pages/verify_otp.php");
        exit();
    }
    
    if (!isset($_SESSION['reset_user_id'])) {
        $_SESSION['error_message'] = "Session expired. Please try again.";
        header("Location: ../pages/forgot_password.php");
        exit();
    }
    
    $user_id = $_SESSION['reset_user_id'];
    $current_time = date("Y-m-d H:i:s");
    
    $stmt = $conn->prepare("SELECT otp, otp_expires_at FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: ../pages/forgot_password.php");
        exit();
    }
    
    if ($user['otp'] !== $otp) {
        $_SESSION['error_message'] = "Invalid OTP code.";
        header("Location: ../pages/verify_otp.php");
        exit();
    }
    
    if (strtotime($user['otp_expires_at']) < time()) {
        $_SESSION['otp_expired'] = true;
        $_SESSION['error_message'] = "OTP has expired. Please request a new one.";
        header("Location: ../pages/verify_otp.php");
        exit();
    }
    
    // OTP is valid
    $_SESSION['otp_verified'] = true; 
    $_SESSION['reset_step'] = 2; // Move to password reset step
    $_SESSION['success_message'] = "OTP verified successfully!";
    header("Location: ../pages/verify_otp.php");
    exit();
    
} elseif ($action === 'resend_otp') {
    // Resend OTP
    if (!isset($_SESSION['reset_user_id'])) {
        $_SESSION['error_message'] = "Session expired. Please start over.";
        header("Location: ../pages/forgot_password.php");
        exit();
    }
    
    $user_id = $_SESSION['reset_user_id'];
    
    $stmt = $conn->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || empty($user['phone'])) {
        $_SESSION['error_message'] = "Unable to resend OTP. Please start over.";
        header("Location: ../pages/forgot_password.php");
        exit();
    }
    
    $phone_number = $user['phone'];
    $otp = random_int(100000, 999999);
    $expiry_time = date("Y-m-d H:i:s", time() + 300); // 5 minutes
    
    $stmt_update = $conn->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE user_id = ?");
    $stmt_update->bind_param("ssi", $otp, $expiry_time, $user_id);
    
    if ($stmt_update->execute()) {
        try {
            $client = new Client($TWILIO_SID, $TWILIO_TOKEN);
            $client->messages->create($phone_number, [
                'from' => $TWILIO_FROM_NUMBER,
                'body' => "Dabbirha Verification Code: $otp. Valid for 5 minutes."
            ]);
            
            $_SESSION['success_message'] = "New verification code sent to your phone ending with " . substr($phone_number, -4);
            $_SESSION['otp_expired'] = false;
            $_SESSION['otp_expiry_time'] = time() + 300; // Store expiry timestamp
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to send SMS. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Failed to generate new OTP. Please try again.";
    }
    
    header("Location: ../pages/verify_otp.php");
    exit();
    
} elseif (isset($_POST['request_otp'])) {
    
    $login_input = trim($_POST['login_input']);

    $stmt = $conn->prepare("SELECT user_id, phone, email FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $login_input, $login_input);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && !empty($user['phone'])) {
        $user_id = $user['user_id'];
        $phone_number = $user['phone'];
        
        $otp = random_int(100000, 999999);
        $expiry_time = date("Y-m-d H:i:s", time() + 300);

        $stmt_update = $conn->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE user_id = ?");
        $stmt_update->bind_param("ssi", $otp, $expiry_time, $user_id);
        
        if ($stmt_update->execute()) {
            try {
                $client = new Client($TWILIO_SID, $TWILIO_TOKEN);
                $client->messages->create($phone_number, [
                    'from' => $TWILIO_FROM_NUMBER,
                    'body' => "Dabbirha Verification Code: $otp. Valid for 5 minutes."
                ]);
                
                $_SESSION['reset_user_id'] = $user_id;
                $_SESSION['reset_step'] = 1;
                $_SESSION['success_message'] = "Verification code sent to your phone ending with " . substr($phone_number, -4);
                $_SESSION['otp_expiry_time'] = time() + 300; // Store expiry timestamp
                header("Location: ../pages/verify_otp.php");
                exit();
                
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to send SMS. Please try again.";
            }
        } else {
            $_SESSION['error_message'] = "Failed to generate OTP. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Account or phone number not found.";
    }
    header("Location: ../pages/forgot_password.php");
    exit();
}

// Default redirect if no valid action
header("Location: ../pages/forgot_password.php");
exit();