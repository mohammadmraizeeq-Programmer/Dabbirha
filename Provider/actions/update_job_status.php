<?php
session_start();
require_once '../../includes/config.php';
header('Content-Type: application/json');

/**
 * UPDATE JOB STATUS ACTIONS
 * Lifecycle Step 4: Provider starts the work
 * Job Status: assigned -> in_progress
 */

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $user_id = $_SESSION['user_id'];

    // Validation: Providers can only trigger the 'in_progress' state
    if (!$job_id || $status !== 'in_progress') {
        echo json_encode(['success' => false, 'message' => 'Invalid status update. Providers can only start the job.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // SECURITY: Verify the user is the provider assigned to this specific job
        // AND check that the current job status is currently 'assigned'
        $stmt = $pdo->prepare("
            SELECT j.job_id 
            FROM jobs j 
            JOIN job_applications ja ON j.job_id = ja.job_id 
            JOIN providers p ON ja.provider_id = p.provider_id 
            WHERE j.job_id = ? 
            AND p.user_id = ? 
            AND ja.application_status = 'assigned'
            AND j.status = 'assigned'
        ");
        $stmt->execute([$job_id, $user_id]);

        if ($stmt->fetch()) {
            // Step 4: Update Job Table to 'in_progress'. 
            // The job_applications status remains 'assigned' until the client closes it.
            $update = $pdo->prepare("UPDATE jobs SET status = 'in_progress', updated_at = NOW() WHERE job_id = ?");
            $update->execute([$job_id]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "Timer started! Good luck with the job."]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Permission denied: Job is not in a state to be started or is not assigned to you.']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Update Job Status Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
    }
}
