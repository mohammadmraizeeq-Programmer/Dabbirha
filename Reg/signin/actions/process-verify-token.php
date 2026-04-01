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
    $stmt = $conn->prepare("SELECT user_id, email, full_name, verified FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['verified'] == 1) {
            $type = 'info';
            $message = "Your email has already been verified.";
        } else {
            $updateStmt = $conn->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE user_id = ?");
            $updateStmt->bind_param("i", $user['user_id']);
            
            if ($updateStmt->execute()) {
                $type = 'success';
                $message = "Your email has been verified successfully!";
                
                // Store verification success in session for redirect
                $_SESSION['verification_success'] = true;
                $_SESSION['verified_email'] = $user['email'];
                $_SESSION['verified_name'] = $user['full_name'];
                
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

// This points from the 'actions' folder to the 'pages' folder
header("Location: ../pages/verify.php"); 
exit();
?>
