<?php
session_start();
require_once '../../includes/config.php';

/**
 * PROCESS HIRE ACTIONS
 * Lifecycle Step 3: Client Hires a Provider
 */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../reg.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'] ?? null;
$method = $_GET['method'] ?? 'cash'; // 'cash' or 'visa' (paypal)
$commission_rate = 0.10;

if (!$job_id) {
    die("Invalid Request: Missing Job ID.");
}

try {
    $pdo->beginTransaction();

    // 1. Identify the application and check job status
    $stmt = $pdo->prepare("
        SELECT ja.application_id, ja.provider_id, ja.quote_amount, j.status as current_status
        FROM job_applications ja
        JOIN jobs j ON ja.job_id = j.job_id
        WHERE ja.job_id = ? 
        AND j.user_id = ? 
        AND ja.application_status = 'pending'
        ORDER BY ja.applied_at DESC
        LIMIT 1
    ");
    $stmt->execute([$job_id, $user_id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$app) {
        throw new Exception("No valid offer found for this job.");
    }

    if ($app['current_status'] !== 'open') {
        throw new Exception("This job cannot be assigned because it is already " . $app['current_status'] . ".");
    }

    $app_id = $app['application_id'];
    $provider_id = $app['provider_id'];
    $quote_amount = $app['quote_amount'];

    // Calculate 10% Commission for this job
    $commission = $quote_amount * $commission_rate;

    // 2. Logic for Debt and Upfront Payment
    $is_paid = ($method === 'visa') ? 1 : 0;

    if ($method === 'cash') {
        // Add to debt because platform hasn't received money yet
        $updateProvider = $pdo->prepare("UPDATE providers SET pending_commission = pending_commission + ? WHERE provider_id = ?");
        $updateProvider->execute([$commission, $provider_id]);
    } 
    // If PayPal/Visa, we do NOT add to debt here; we deduct it during complete_job.php

    // 3. Update Job Status
    $updateJob = $pdo->prepare("
        UPDATE jobs 
        SET status = 'assigned', 
            payment_method = ?, 
            commission_amount = ?, 
            is_paid = ? 
        WHERE job_id = ? AND user_id = ?
    ");
    $updateJob->execute([$method, $commission, $is_paid, $job_id, $user_id]);

    // 4. Update Application Statuses
    $pdo->prepare("UPDATE job_applications SET application_status = 'assigned' WHERE application_id = ?")
        ->execute([$app_id]);

    $pdo->prepare("UPDATE job_applications SET application_status = 'rejected' WHERE job_id = ? AND application_id != ?")
        ->execute([$job_id, $app_id]);

    $pdo->commit();
    header("Location: ../pages/dashboard.php?msg=HiredSuccessfully");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Hire Process Error: " . $e->getMessage());
    header("Location: ../pages/dashboard.php?error=" . urlencode($e->getMessage()));
    exit;
}