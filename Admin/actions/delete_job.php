<?php
include '../../includes/config.php';

// Check if ID is provided
if (isset($_POST['id'])) {
    $job_id = (int)$_POST['id'];

    try {
        $pdo->beginTransaction();

        // 1. Delete associated applications first (Foreign Key constraint)
        $stmt1 = $pdo->prepare("DELETE FROM job_applications WHERE job_id = ?");
        $stmt1->execute([$job_id]);

        // 2. Delete the job
        $stmt2 = $pdo->prepare("DELETE FROM jobs WHERE job_id = ?");
        $stmt2->execute([$job_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Job deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
}
?>