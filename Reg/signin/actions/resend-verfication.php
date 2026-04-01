<?php
session_start();
include "../../../includes/config.php";
require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Get email from request
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'error' => 'No email provided']);
    exit;
}

// Check if user exists and is not verified
$stmt = $conn->prepare("SELECT user_id, full_name, verification_token FROM users WHERE email = ? AND verified = 0");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'User not found or already verified']);
    exit;
}

$user = $result->fetch_assoc();

// Generate new token if needed
if (empty($user['verification_token'])) {
   // Always generate a fresh token for a resend request
$new_token = bin2hex(random_bytes(16));
$updateStmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE user_id = ?");
$updateStmt->bind_param("si", $new_token, $user['user_id']);
$updateStmt->execute();

// Use the fresh token for the email
$user['verification_token'] = $new_token;
}

// Send verification email
function sendVerificationEmail($email, $full_name, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mazenawaideh22@gmail.com';
        $mail->Password   = 'yypb cftn jzce spxx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('mazenawaideh22@gmail.com', 'Dabbirha');
        $mail->addAddress($email, $full_name);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Dabbirha Account';
        $verificationLink = "http://localhost/Dabbirha/Reg/signin/pages/verify.php?token=$token";
        $mail->Body    = "<h2>Hello $full_name,</h2>
                          <p>We received a request to resend your verification link.</p>
                          <p>Click the link below to verify your email:</p>
                          <a href='$verificationLink'>$verificationLink</a>
                          <p>If you didn't request this, please ignore this email.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Send the email
if (sendVerificationEmail($email, $user['full_name'], $user['verification_token'])) {
    // Add the 'message' key here so JavaScript can display it
    echo json_encode([
        'success' => true,
        'message' => 'A new verification email has been sent successfully!'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to send email. Please try again later.'
    ]);
}
exit;
?>