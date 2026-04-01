<?php
session_start();
include "../../../includes/config.php";
require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json");

// ---------------------------
// VERIFICATION EMAIL FUNCTION
// ---------------------------
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
        $verificationLink = "http://localhost/Dabbirha/Reg/signin/actions/process-verify-token.php?token=$token";
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #1e293b;'>Welcome to Dabbirha, $full_name!</h2>
                <p>Thank you for creating your account. Please verify your email address to complete your registration:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$verificationLink' style='background-color: #1e293b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Verify Email Address
                    </a>
                </div>
                <p>Or copy and paste this link in your browser:</p>
                <p style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; word-break: break-all;'>
                    $verificationLink
                </p>
                <p>This link will expire in 24 hours.</p>
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px;'>
                    If you didn't create an account with Dabbirha, please ignore this email.
                </p>
            </div>
        ";
        
        $mail->AltBody = "Welcome to Dabbirha!\n\nPlease verify your email by clicking this link:\n$verificationLink\n\nThis link will expire in 24 hours.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}

// ---------------------------
// READ GOOGLE TOKEN
// ---------------------------
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? "";

if (!$token) {
    echo json_encode(["success" => false, "error" => "No token provided"]); 
    exit;
}

// VERIFY GOOGLE TOKEN
$verifyUrl = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($token);
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$response = @file_get_contents($verifyUrl, false, $context);

if (!$response) {
    // Try with cURL as fallback
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
}

if (!$response) {
    echo json_encode(["success" => false, "error" => "Cannot verify Google token"]);
    exit;
}

$googleData = json_decode($response, true);

if (isset($googleData['error'])) {
    echo json_encode(["success" => false, "error" => "Google token error: " . $googleData['error']]);
    exit;
}

// Check Client ID
$clientId = "84776159395-u2bg538ej9gb816uuvvsesc9r12o82jh.apps.googleusercontent.com";
if (!isset($googleData["aud"]) || $googleData["aud"] !== $clientId) {
    echo json_encode(["success" => false, "error" => "Invalid token audience. Expected: $clientId, Got: " . ($googleData["aud"] ?? 'none')]);
    exit;
}

// Extract user data
$email = $googleData["email"] ?? '';
$name  = $googleData["name"] ?? ($googleData["email"] ?? 'User');
$googleId = $googleData["sub"] ?? '';

if (empty($email)) {
    echo json_encode(["success" => false, "error" => "No email in Google response"]);
    exit;
}

// ---------------------------
// CHECK IF USER EXISTS
// ---------------------------
$stmt = $conn->prepare("SELECT user_id, full_name, email, role, verified, google_id FROM users WHERE email = ? OR google_id = ?");
$stmt->bind_param("ss", $email, $googleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // EXISTING USER
    $user = $result->fetch_assoc();
    
    // Update google_id if not set
    if (empty($user['google_id'])) {
        $updateStmt = $conn->prepare("UPDATE users SET google_id = ? WHERE user_id = ?");
        $updateStmt->bind_param("si", $googleId, $user['user_id']);
        $updateStmt->execute();
    }
    
    // Check if user is verified
    if (!$user['verified']) {
        // User exists but not verified - resend verification
        $verification_token = bin2hex(random_bytes(32));
        $updateStmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE user_id = ?");
        $updateStmt->bind_param("si", $verification_token, $user['user_id']);
        $updateStmt->execute();
        
        // Send verification email
        sendVerificationEmail($email, $user['full_name'], $verification_token);
        
        echo json_encode([
            "success" => true, 
            "redirect" => "/Dabbirha/Reg/signin/pages/verify_notice.php",
            "email" => $email
        ]);
        exit;
    }

    // User is verified - log them in
    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["full_name"] = $user["full_name"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["role"] = $user["role"];
    
    // Get provider_id if user is a provider
    if ($user["role"] === "provider") {
        $providerStmt = $conn->prepare("SELECT provider_id FROM providers WHERE user_id = ?");
        $providerStmt->bind_param("i", $user['user_id']);
        $providerStmt->execute();
        $providerResult = $providerStmt->get_result();
        if ($providerResult->num_rows === 1) {
            $provider = $providerResult->fetch_assoc();
            $_SESSION['provider_id'] = $provider['provider_id'];
        }
    }
    
    // Set redirect based on role
    $redirectPage = "../../../User/pages/dashboard.php"; // default
    if ($user["role"] === "admin") {
        $redirectPage = "admin.php";
    } elseif ($user["role"] === "provider") {
        $redirectPage = "../provider/provider-dashboard.php";
    }
    
    echo json_encode([
        "success" => true, 
        "redirect" => $redirectPage,
        "role" => $user["role"]
    ]);
    exit;
    
} else {
    // NEW USER → CREATE ACCOUNT
    $password_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // Generate random password
    $verification_token = bin2hex(random_bytes(32));
    $role = 'user'; // Default role for Google signup

    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, password, google_id, role, verified, verification_token, created_at)
        VALUES (?, ?, ?, ?, ?, 0, ?, NOW())
    ");
    $stmt->bind_param("ssssss", $name, $email, $password_hash, $googleId, $role, $verification_token);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // Log user in immediately
        $_SESSION["user_id"] = $user_id;
        $_SESSION["full_name"] = $name;
        $_SESSION["email"] = $email;
        $_SESSION["role"] = $role;
        
        // Send verification email
        sendVerificationEmail($email, $name, $verification_token);
        
        echo json_encode([
            "success" => true, 
            "redirect" =>  "/Dabbirha/Reg/signin/pages/verify_notice.php",
            "email" => $email,
            "message" => "Account created. Please check your email for verification."
        ]);
        exit;
    } else {
        error_log("Database error: " . $conn->error);
        echo json_encode([
            "success" => false, 
            "error" => "Database error: " . ($conn->error ?: "Unknown error")
        ]);
        exit;
    }
}
?>