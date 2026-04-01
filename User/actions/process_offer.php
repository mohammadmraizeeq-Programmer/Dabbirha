<?php
session_start();
require_once '../../includes/config.php';
header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'] ?? null;
    $action = $_POST['action'] ?? null;

    // We only use this file for 'decline' now. 
    // 'accept' is handled by process_hire.php
    if (!$application_id || $action !== 'decline') {
        echo json_encode(['success' => false, 'message' => 'Invalid Request.']);
        exit;
    }

    try {
        // Simple Decline: Just mark this specific application as rejected
        $stmt = $pdo->prepare("
            UPDATE job_applications ja
            JOIN jobs j ON ja.job_id = j.job_id
            SET ja.application_status = 'rejected' 
            WHERE ja.application_id = ? AND j.user_id = ?
        ");
        
        $stmt->execute([$application_id, $_SESSION['user_id']]);

        echo json_encode(['success' => true, 'message' => 'Offer declined successfully.']);
        
    } catch (Exception $e) {
        error_log("Decline Offer Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
}