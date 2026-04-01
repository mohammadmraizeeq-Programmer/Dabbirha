<?php
header('Content-Type: application/json');

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../includes/config.php";
require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------------------------
// EMAIL VERIFICATION FUNCTION
// ---------------------------
function sendVerificationEmail($email, $full_name, $token, $type = 'provider') {
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
        $verificationLink = "http://localhost/Dabbirha/Reg/signup_provider/actions/process-verify-token-2.php?token=$token";
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #1e293b;'>Welcome to Dabbirha, $full_name!</h2>
                <p>Thank you for registering as a service provider. Please verify your email address to complete your registration:</p>
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
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Check if it's an AJAX request
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get all form data
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $primary_expertise = $_POST['primary_expertise'] ?? '';
        $hourly_rate = $_POST['hourly_rate'] ?? '';
        $services = $_POST['services'] ?? '';
        $skills = $_POST['skills'] ?? '';
        $about_me = $_POST['about_me'] ?? '';
        $address = $_POST['address'] ?? '';
        $latitude = $_POST['latitude'] ?? '';
        $longitude = $_POST['longitude'] ?? '';
        $service_radius = $_POST['service_radius'] ?? 10;
        $google_signup = $_POST['google_signup'] ?? '0';
        $google_id = $_POST['google_id'] ?? '';
        
        // Validation
        if (empty($full_name) || empty($email) || empty($phone) || empty($password) || 
            empty($primary_expertise) || empty($hourly_rate) || empty($about_me) || 
            empty($address) || empty($latitude) || empty($longitude)) {
            throw new Exception("Please fill in all required fields.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }
        
        if (!preg_match('/^[0-9]{9}$/', $phone)) {
            throw new Exception("Please enter a valid 9-digit Jordanian phone number.");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }
        
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }
        
        if (!is_numeric($hourly_rate) || floatval($hourly_rate) < 5 || floatval($hourly_rate) > 100) {
            throw new Exception("Hourly rate must be between 5 and 100 JOD.");
        }
        if (empty(trim($about_me))) {
            throw new Exception("About you section is required.");
        }
        
        if (empty($services)) {
            throw new Exception("Please select at least one service.");
        }
        
        if (empty($skills)) {
            throw new Exception("Please select at least one skill.");
        }
        
        // Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            throw new Exception("Email already registered.");
        }
        
        // Handle profile picture upload - FIXED PATH
        $profile_picture_url = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_extensions)) {
                // FIXED: Changed to absolute path for better reliability
               // Define the absolute path
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Dabbirha/uploads/providers/';

// Create directory if it doesn't exist (with recursive flag)
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate a unique filename to avoid overwrites
$file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
$new_filename = uniqid('provider_') . '.' . $file_extension;
$target_file = $upload_dir . $new_filename;

// Move the file from temp to target
if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
    // Save this relative path to your database
    $profile_picture_url = '/Dabbirha/uploads/providers/' . $new_filename;
} else {
    throw new Exception("Failed to move uploaded file. Check folder permissions.");
}
            }
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert into users table
            $verification_token = bin2hex(random_bytes(32));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Format phone number with country code
            $formatted_phone = '+962' . $phone;
            
            $user_stmt = $conn->prepare("
                INSERT INTO users 
                (full_name, email, password, phone, role, verified, verification_token, created_at)
                VALUES (?, ?, ?, ?, 'provider', 0, ?, NOW())
            ");
            $user_stmt->bind_param(
                "sssss",
                $full_name,
                $email,
                $hashed_password,
                $formatted_phone,
                $verification_token
            );
            
            if (!$user_stmt->execute()) {
                throw new Exception("Failed to create user account: " . $user_stmt->error);
            }
            
            $user_id = $user_stmt->insert_id;
            
            // Insert into providers table - MATCHING YOUR DATABASE STRUCTURE
            $provider_stmt = $conn->prepare("
                INSERT INTO providers 
                (user_id, services, base_service, image, bio, hourly_rate, latitude, longitude, address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            // Cast latitude and longitude to float for decimal(10,6) format
            $latitude_float = (float)$latitude;
            $longitude_float = (float)$longitude;
            $hourly_rate_decimal = (float)$hourly_rate;
            
            $provider_stmt->bind_param(
                "issssddds",
                $user_id,
                $services,
                $primary_expertise,
                $profile_picture_url,
                $about_me,
                $hourly_rate_decimal,
                $latitude_float,
                $longitude_float,
                $address
            );
            
            if (!$provider_stmt->execute()) {
                throw new Exception("Failed to create provider profile: " . $provider_stmt->error);
            }
            
            $provider_id = $provider_stmt->insert_id;
            
            // Insert skills - MATCHING YOUR DATABASE STRUCTURE
            if (!empty($skills)) {
                $skills_array = array_map('trim', explode(',', $skills));
                $skills_array = array_filter($skills_array);
                
                if (!empty($skills_array)) {
                    // Get next skill_id
                    $max_id_result = $conn->query("SELECT MAX(skill_id) as max_id FROM skills");
                    $max_id_row = $max_id_result->fetch_assoc();
                    $next_id = ($max_id_row['max_id'] !== null) ? $max_id_row['max_id'] + 1 : 1;
                    
                    $skill_stmt = $conn->prepare("INSERT INTO skills (skill_id, provider_id, skill_name) VALUES (?, ?, ?)");
                    
                    if (!$skill_stmt) {
                        throw new Exception("Failed to prepare skills statement: " . $conn->error);
                    }
                    
                    foreach ($skills_array as $skill) {
                        $skill = substr(trim($skill), 0, 100);
                        if (!empty($skill)) {
                            $skill_stmt->bind_param("iis", $next_id, $provider_id, $skill);
                            if (!$skill_stmt->execute()) {
                                throw new Exception("Failed to insert skill: " . $skill_stmt->error);
                            }
                            $next_id++;
                        }
                    }
                    $skill_stmt->close();
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            // Send verification email
            $emailSent = sendVerificationEmail($email, $full_name, $verification_token);
            
            if ($emailSent) {
                $response['success'] = true;
                $response['message'] = 'Registration successful! Please check your email to verify your account.';
                $response['redirect'] = 'pages/verify_notice_2.php?email=' . urlencode($email);
                
                // Clear session data
                if (isset($_SESSION['signup_data'])) {
                    unset($_SESSION['signup_data']);
                }
                if (isset($_SESSION['verified_email'])) {
                    unset($_SESSION['verified_email']);
                }
            } else {
                $response['success'] = true;
                $response['message'] = 'Account created but verification email failed to send. Please contact support.';
                $response['redirect'] = 'pages/verify_notice_2.php?email=' . urlencode($email);
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception($e->getMessage());
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit;
?>
