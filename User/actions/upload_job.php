<?php
/**
 * Action: Upload Job
 * Location: /User/actions/upload_job.php
 */

session_start();

// 1. Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Load Database Configuration
require_once __DIR__ . '/../../includes/config.php';

if (!isset($pdo)) {
    $_SESSION['error'] = "Critical System Error: Database connection lost.";
    header('Location: ../pages/upload_problem.php');
    exit;
}

// 3. Security: Authentication Check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to submit a request.";
    header('Location: ../../../Reg/signin/pages/signin.php');
    exit;
}
$user_id = $_SESSION['user_id'];

try {
    // 4. Collect and Sanitize Form Data
    $target_provider_id = !empty($_POST['target_provider_id']) ? $_POST['target_provider_id'] : null;
    $ai_category       = $_POST['ai_category_suggestion'] ?? 'general_maintenance';
    $problem_title     = $_POST['problem_title'] ?? 'New Service Request';
    $urgency           = $_POST['urgency_level'] ?? 'medium';
    $description       = $_POST['problem_description'] ?? '';
    $phone             = $_POST['contact_phone'] ?? '';
    $property_type     = $_POST['property_type'] ?? 'apartment';
    $address           = $_POST['service_address'] ?? '';
    
    // Check if location_pin exists; if not (from direct_request), provide a default to prevent crash
    $location_pin      = $_POST['location_pin'] ?? '0,0'; 
    $is_direct_step    = isset($_POST['is_direct_step']);

    // Basic Validation
    if (empty($phone) || empty($address)) {
        throw new Exception("Contact phone and service address are required.");
    }

    // 5. File Upload Logic (Standard File or Base64)
    $file_path_db = "";
    $upload_dir = __DIR__ . '/../uploads/jobs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_POST['image_base64'])) {
        // Handle Base64 from Canvas/Camera
        $data = $_POST['image_base64'];
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]);
            $data = base64_decode($data);
            if ($data !== false) {
                $file_name = time() . '.' . $type;
                file_put_contents($upload_dir . $file_name, $data);
                $file_path_db = "User/uploads/jobs/" . $file_name;
            }
        }
    } elseif (isset($_FILES['problem_media']) && $_FILES['problem_media']['error'] === UPLOAD_ERR_OK) {
        // Handle standard file upload from direct_request.php
        $file_ext = pathinfo($_FILES['problem_media']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '.' . $file_ext;
        if (move_uploaded_file($_FILES['problem_media']['tmp_name'], $upload_dir . $file_name)) {
            $file_path_db = "User/uploads/jobs/" . $file_name;
        }
    }

    // 6. Construct Detailed Description
    $full_description = "Property: $property_type\nContact: $phone\n---\n$description";

    // 7. Database Insertion
    $sql = "INSERT INTO jobs (user_id, title, description, file_path, service_type, location_address, location_pin, urgency_level, status, target_provider_id) 
            VALUES (:u, :t, :d, :f, :s, :a, :p, :ur, 'open', :tpid)";

    $stmt = $pdo->prepare($sql);
    $params = [
        ':u'    => $user_id,
        ':t'    => $problem_title,
        ':d'    => $full_description,
        ':f'    => $file_path_db,
        ':s'    => $ai_category,
        ':a'    => $address,
        ':p'    => $location_pin,
        ':ur'   => $urgency,
        ':tpid' => $target_provider_id
    ];

    if (!$stmt->execute($params)) {
        throw new Exception("Database Insert Failed.");
    }

    $new_job_id = $pdo->lastInsertId();

    // 8. Redirect Logic
    if ($is_direct_step) {
        // If coming from direct_request, go to Step 2 of upload_problem
        header("Location: ../pages/upload_problem.php?step=2&job_id=" . $new_job_id);
        exit;
    } elseif (!$target_provider_id) {
        // General Request: Run AI analysis
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/Dabbirha/User/actions/ai_analyze_google.php?job_id=" . $new_job_id);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        header("Location: ../pages/dashboard.php?msg=RequestAnalyzed");
        exit;
    } else {
        // Direct request already finished (standard path)
        header("Location: ../pages/dashboard.php?msg=DirectRequestSent");
        exit;
    }

} catch (Exception $e) {
    error_log("Upload Job Error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}