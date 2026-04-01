<?php
/**
 * Dabbirha - AI-Powered Home Services Platform
 * Configuration Template
 * * Instructions: 
 * 1. Rename this file to 'config.php'
 * 2. Fill in your local database credentials below.
 */

// --- 1. Database Configuration (REPLACE WITH YOUR CREDENTIALS) ---
$host = 'localhost';
$username = 'YOUR_DATABASE_USERNAME'; // e.g., 'root'
$password = 'YOUR_DATABASE_PASSWORD'; // e.g., ''
$database = 'YOUR_DATABASE_NAME';     // e.g., 'Dabbirha'

// --- 2. MySQLi Connection ---
$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed (MySQLi): " . mysqli_connect_error());
}

// Define constants for PDO and other uses
define('DB_HOST', $host);
define('DB_NAME', $database);
define('DB_USER', $username);
define('DB_PASS', $password);

// --- 3. PDO Connection (For chat and newer scripts) ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("PDO Database connection failed: " . $e->getMessage());
    $pdo = null; 
}

// --- 4. Helper Functions ---

/**
 * Fetches the full name of a user based on their ID and role.
 */
function get_user_details_for_chat($pdo, $user_id, $user_role) {
    if (!$pdo) {
        return ['full_name' => 'DB Disconnected'];
    }

    $valid_roles = ['student', 'teacher', 'provider', 'user', 'admin'];
    if (!in_array($user_role, $valid_roles)) {
        return ['full_name' => 'Invalid Role'];
    }

    $table = $user_role . 's'; 
    
    try {
        $stmt = $pdo->prepare("SELECT full_name FROM $table WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        return $user ?: ['full_name' => 'User Not Found'];
    } catch (PDOException $e) {
        error_log("Error fetching user details for chat: " . $e->getMessage());
        return ['full_name' => 'DB Query Error'];
    }
}

// --- 5. Base URL Path Logic ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host_name = $_SERVER['HTTP_HOST'];

$current_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$parts = explode('/', trim($current_dir, '/'));
$base_path = '/' . ($parts[0] ?? 'Dabbirha'); 

define('BASE_URL', $protocol . '://' . $host_name . $base_path);

// --- 6. Twilio Configuration ---
define('TWILIO_SID', 'YOUR_TWILIO_SID');
define('TWILIO_TOKEN', 'YOUR_TWILIO_TOKEN');
define('TWILIO_FROM_NUMBER', 'YOUR_TWILIO_PHONE_NUMBER');
?>