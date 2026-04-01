<?php
session_start();
require_once "../../includes/config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'] ?? null;
    $provider_id = $_POST['provider_id'] ?? null;
    $price = $_POST['price'] ?? null;
    $message = $_POST['message'] ?? null;

    if (!$job_id || !$price) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    try {
        // Insert into job_applications table (using the columns from your SQL)
        $sql = "INSERT INTO job_applications (job_id, provider_id, quote_amount, message, application_status) 
                VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$job_id, $provider_id, $price, $message]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}