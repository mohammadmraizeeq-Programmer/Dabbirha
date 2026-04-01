<?php
ob_start(); // 1. Added this to prevent "Headers already sent" errors
session_start();

// 2. Database Connection - Path out of actions/signin/Reg to includes/
include "../../../includes/config.php"; 
require '../../../vendor/autoload.php'; // PHPMailer



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------------------------
// HELPER FUNCTION TO SEND VERIFICATION EMAIL
// ---------------------------
function sendVerificationEmail($email, $full_name, $token, $type = 'user')
{
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
                <h2 style='color: #1e293b'>Welcome to Dabbirha, $full_name!</h2>
                <p>Thank you for creating your account. Please verify your email address to complete your registration:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$verificationLink' style='background-color: #1e293b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Verify Email Address
                    </a>
                </div>
                <p>Or copy and paste this link in your browser:</p>
                <p style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; word-break: break-all;'>$verificationLink</p>
                <p>This link will expire in 24 hours.</p>
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px;'>If you didn't create an account with Dabbirha, please ignore this email.</p>
            </div>";

        $mail->AltBody = "Welcome to Dabbirha!\n\nPlease verify your email by clicking this link:\n$verificationLink";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// ---------------------------
// SIGN UP LOGIC
// ---------------------------
if (isset($_POST['signup'])) {
    $full_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone_partial = trim($_POST['phone_partial']);

    $country_code = '+962';
    $phone = $country_code . ltrim($phone_partial, '+');

    if (!preg_match('/^\+9627\d{8}$/', $phone)) {
        $signup_error = "Please enter a valid Jordanian phone number (e.g., 7XXXXXXXX)";
    }
    
    $role = 'user';

    if (!isset($_POST['privacy_policy'])) {
        $signup_error = "You must accept the Terms of Service and Privacy Policy.";
    } elseif (empty($full_name) || empty($email) || empty($password)) {
        $signup_error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $signup_error = "Email is already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(16));

            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role, verified, verification_token, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, NOW())");
            $stmt->bind_param("ssssss", $full_name, $email, $password_hash, $phone, $role, $verification_token);

            if ($stmt->execute()) {
                sendVerificationEmail($email, $full_name, $verification_token);
                
                echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            title: "Success!",
                            html: "Signup successful!<br>Please check your email to verify your account.",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#88add2"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "../pages/signin.php";
                            }
                        });
                    });
                </script>';
                exit;
            } else {
                $signup_error = "Registration failed. Try again.";
            }
        }
    }
}

// ---------------------------
// SIGN IN LOGIC
// ---------------------------
if (isset($_POST['signin'])) {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        header("Location: ../pages/signin.php?error=empty");
        exit();
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, email, phone, role, password, verified FROM users WHERE email = ? OR full_name = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (!$user['verified']) {
                header("Location: ../pages/signin.php?error=unverified");
                exit();
            } elseif (password_verify($password, $user['password'])) {
                // Set Session Data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === "provider") {
                    $provider_stmt = $conn->prepare("SELECT provider_id FROM providers WHERE user_id = ?");
                    $provider_stmt->bind_param("i", $user['user_id']);
                    $provider_stmt->execute();
                    $provider_result = $provider_stmt->get_result();
                    if ($provider_result->num_rows === 1) {
                        $provider = $provider_result->fetch_assoc();
                        $_SESSION['provider_id'] = $provider['provider_id'];
                    }
                }

                // --- UPDATED DIRECTORY PATHS ---
                if ($user['role'] === "admin") {
                    header("Location: ../../../Admin/pages/dashboard.php");
                } elseif ($user['role'] === "provider") {
                    header("Location: ../../../Provider/pages/provider_dashboard.php");
                } else {
                    header("Location: ../../../User/pages/dashboard.php");
                }
                exit();
            } else {
                header("Location: ../pages/signin.php?error=invalid");
                exit();
            }
        } else {
            header("Location: ../pages/signin.php?error=notfound");
            exit();
        }
    }
}

ob_end_flush(); // Close the output buffer
?>