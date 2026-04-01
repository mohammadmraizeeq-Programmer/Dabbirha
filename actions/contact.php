<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include "../includes/config.php";

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Check if it's an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Optional: Rate limiting check
checkRateLimit();

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Validate required fields
$required = ['name', 'email', 'service_type', 'message'];
$errors = [];

// Sanitize inputs
$name = trim(filter_var($_POST['name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
$phone_partial = isset($_POST['phone_partial']) ? trim($_POST['phone_partial']) : '';
$service_type = trim(filter_var($_POST['service_type'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$message = trim(filter_var($_POST['message'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

// Validation
if (empty($name) || strlen($name) < 2) {
    $errors['name'] = 'Please enter a valid name (minimum 2 characters)';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}

// Process phone number - combine with country code
$country_code = '+962';
$phone = '';

if (!empty($phone_partial)) {
    // Remove any non-digit characters
    $phone_partial = preg_replace('/[^\d]/', '', $phone_partial);
    
    // Validate Jordanian mobile format (must start with 7 and be 9 digits)
    if (!preg_match('/^7\d{8}$/', $phone_partial)) {
        $errors['phone'] = 'Please enter a valid Jordanian mobile number (e.g., 712345678)';
    } else {
        // Combine with country code
        $phone = $country_code . $phone_partial;
    }
}

if (empty($service_type)) {
    $errors['service_type'] = 'Please select a service type';
}

if (empty($message) || strlen($message) < 10) {
    $errors['message'] = 'Please enter a message (minimum 10 characters)';
}

// Validate message length
if (strlen($message) > 1000) {
    $errors['message'] = 'Message is too long (maximum 1000 characters)';
}

// If there are validation errors
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Validation failed', 
        'errors' => $errors
    ]);
    exit;
}

// Get user IP and browser info
$ip_address = getClientIP();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

try {
    // Start transaction
    if ($pdo) {
        $pdo->beginTransaction();
    } elseif ($conn) {
        mysqli_begin_transaction($conn);
    }
    
    // Prepare SQL statement
    if (isset($pdo)) {
        // PDO version
        $sql = "INSERT INTO contact_messages 
                (name, email, phone, service_type, message, ip_address, user_agent, status, created_at, updated_at)
                VALUES (:name, :email, :phone, :service_type, :message, :ip_address, :user_agent, 'new', NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':service_type', $service_type, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
        $stmt->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $message_id = $pdo->lastInsertId();
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }
        } else {
            throw new Exception('Failed to save message to database');
        }
    } elseif (isset($conn)) {
        // MySQLi version
        $stmt = $conn->prepare("
            INSERT INTO contact_messages 
            (name, email, phone, service_type, message, ip_address, user_agent, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'new', NOW(), NOW())
        ");
        
        $stmt->bind_param(
            "sssssss", 
            $name, 
            $email, 
            $phone, 
            $service_type, 
            $message, 
            $ip_address, 
            $user_agent
        );
        
        if ($stmt->execute()) {
            $message_id = $stmt->insert_id;
            mysqli_commit($conn);
        } else {
            throw new Exception('Failed to save message to database: ' . $stmt->error);
        }
        $stmt->close();
    } else {
        throw new Exception('No database connection available');
    }
    
    // Send email notification to admin
    sendAdminNotification([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'service_type' => $service_type,
        'message' => $message
    ], $message_id ?? 0);
    
    $response['success'] = true;
    $response['message'] = 'Your message has been sent successfully! We\'ll contact you soon.';
    $response['message_id'] = $message_id ?? 0;
    
    // Log successful submission
    error_log("Contact form submitted - ID: " . ($message_id ?? 0) . ", Email: {$email}");
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    error_log("Contact Form Database Error (PDO): " . $e->getMessage());
    
    http_response_code(500);
    $response['message'] = 'A database error occurred. Please try again later.';
    
} catch (mysqli_sql_exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    
    // Log error
    error_log("Contact Form Database Error (MySQLi): " . $e->getMessage());
    
    http_response_code(500);
    $response['message'] = 'A database error occurred. Please try again later.';
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
exit;

/**
 * Get client IP address
 */
function getClientIP() {
    $ip_address = '';
    
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        $ip_address = $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip_address = 'UNKNOWN';
    }
    
    return $ip_address;
}

/**
 * Send email notification to admin
 */
function sendAdminNotification($data, $message_id) {
    // Configure your email settings here
    $to = "admin@dabbirha.com"; // Change to your admin email
    $subject = "New Contact Form Message #" . $message_id;
    
    $message = "
    <html>
    <head>
        <title>New Contact Message</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #0d6efd; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
            .content { background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
            .field { margin-bottom: 15px; }
            .field-label { font-weight: bold; color: #495057; }
            .field-value { color: #212529; }
            .message-box { background-color: white; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>📧 New Contact Form Submission</h2>
                <p>Message ID: #{$message_id}</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='field-label'>👤 Name:</div>
                    <div class='field-value'>{$data['name']}</div>
                </div>
                
                <div class='field'>
                    <div class='field-label'>📧 Email:</div>
                    <div class='field-value'>{$data['email']}</div>
                </div>
                
                <div class='field'>
                    <div class='field-label'>📱 Phone:</div>
                    <div class='field-value'>" . ($data['phone'] ?: 'Not provided') . "</div>
                </div>
                
                <div class='field'>
                    <div class='field-label'>🔧 Service Type:</div>
                    <div class='field-value'>" . ($data['service_type'] ? ucfirst($data['service_type']) : 'Not specified') . "</div>
                </div>
                
                <div class='field'>
                    <div class='field-label'>💬 Message:</div>
                    <div class='message-box'>
                        " . nl2br(htmlspecialchars($data['message'])) . "
                    </div>
                </div>
                
                <div class='field'>
                    <div class='field-label'>🕒 Submitted:</div>
                    <div class='field-value'>" . date('F j, Y, g:i a') . "</div>
                </div>
                
                <hr style='margin: 30px 0; border-color: #dee2e6;'>
                
                <p style='color: #6c757d; font-size: 14px;'>
                    <strong>Action Required:</strong> Please respond within 24 hours.
                    <br>
                    <strong>IP Address:</strong> " . getClientIP() . "
                </p>
                
                <p style='margin-top: 20px;'>
                    <a href='" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/admin/contact_messages.php' 
                       style='background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       View in Admin Panel
                    </a>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Dabbirha Contact Form <no-reply@dabbirha.com>" . "\r\n";
    $headers .= "Reply-To: {$data['email']}" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "X-Priority: 1 (Highest)" . "\r\n";
    $headers .= "X-MSMail-Priority: High" . "\r\n";
    $headers .= "Importance: High" . "\r\n";
    
    // Use your existing email sending method (SendGrid, etc.)
    @mail($to, $subject, $message, $headers);
}

/**
 * Rate limiting function (optional)
 */
function checkRateLimit() {
    $ip = getClientIP();
    $key = 'contact_limit_' . $ip;
    
    // Check if this IP has submitted recently
    if (isset($_SESSION[$key])) {
        $last_submission = $_SESSION[$key];
        $time_passed = time() - $last_submission;
        
        // Limit to 3 submissions per 15 minutes
        if ($time_passed < 900) { // 15 minutes in seconds
            if (!isset($_SESSION[$key . '_count'])) {
                $_SESSION[$key . '_count'] = 1;
            } else {
                $_SESSION[$key . '_count']++;
            }
            
            if ($_SESSION[$key . '_count'] > 3) {
                http_response_code(429); // Too Many Requests
                echo json_encode([
                    'success' => false, 
                    'message' => 'Too many submissions. Please wait 15 minutes before trying again.'
                ]);
                exit;
            }
        } else {
            // Reset counter after 15 minutes
            unset($_SESSION[$key . '_count']);
        }
    }
    
    // Update last submission time
    $_SESSION[$key] = time();
}
?>