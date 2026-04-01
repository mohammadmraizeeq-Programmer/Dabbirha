<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];
    try {
        // 1. Verify Ownership (Security Check)
        // Prevent a user from deleting someone else's job by manipulating the ID in the console
        $checkStmt = $pdo->prepare("SELECT description FROM jobs WHERE job_id = ? AND user_id = ?");
        $checkStmt->execute([$job_id, $user_id]);
        $job = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            echo json_encode(['success' => false, 'message' => 'Job not found or permission denied.']);
            exit;
        }

        // 2. Optional: Cleanup Physical Files
        // If your job description contains an "Attachment URL", we should delete that file from /uploads/
        if (preg_match('/Attachment URL: (.*)/', $job['description'], $matches)) {
            $file_path = __DIR__ . '/../..' . trim($matches[1]);
            if (file_exists($file_path)) {
                unlink($file_path); // Physically remove the image from the server
            }
        }

        // 3. Delete the Job
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id = ? AND user_id = ?");
        $result = $stmt->execute([$job_id, $user_id]);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete job from database.']);
        }
    } catch (PDOException $e) {
        error_log("Delete Job Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
