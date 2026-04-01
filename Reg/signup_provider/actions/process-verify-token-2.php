<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../includes/config.php";

$token = $_GET['token'] ?? '';
$message = '';
$type = 'error';

if (!$token) {
    $message = "Invalid verification link";
} else {
    // Check if token exists and user is not verified
    $stmt = $conn->prepare("SELECT user_id, email, full_name, verified FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['verified'] == 1) {
            $type = 'info';
            $message = "Your email has already been verified.";
            // FIX 1: Even if already verified, re-set the session email 
            // so Step 5 knows who the user is if they click the link again.
            $_SESSION['verified_email'] = $user['email'];
        } else {
            $updateStmt = $conn->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE user_id = ?");
            $updateStmt->bind_param("i", $user['user_id']);
            
            if ($updateStmt->execute()) {
                $type = 'success';
                $message = "Your email has been verified successfully!";
                
                // FIX 2: Essential session variables for provider-registration.php
                $_SESSION['verification_success'] = true;
                $_SESSION['verified_email'] = $user['email'];
                $_SESSION['verified_name'] = $user['full_name'];
                
                // FIX 3: Initialize signup_data if it doesn't exist to prevent "Empty" info
                if (!isset($_SESSION['signup_data'])) {
                    $_SESSION['signup_data'] = [
                        'full_name' => $user['full_name'],
                        'email' => $user['email']
                    ];
                } else {
                    // Update existing session with verified data
                    $_SESSION['signup_data']['email'] = $user['email'];
                    $_SESSION['signup_data']['full_name'] = $user['full_name'];
                }
                
            } else {
                $message = "Unable to complete verification. Please try again.";
            }
        }
    } else {
        $message = "This link has expired or is invalid.";
    }
}

$_SESSION['verify_type'] = $type;
$_SESSION['verify_message'] = $message;

// FIX 4: Ensure session is written before redirect
session_write_close(); 

header("Location: ../pages/verify_2.php");
exit();
?>