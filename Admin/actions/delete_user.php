<?php
session_start();
require_once '../../includes/config.php';

// 1. Security Check: Ensure only logged-in admins can access this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../Reg/signin/pages/signin.php");
    exit();
}

// 2. Validate Request
if (isset($_GET['id'])) {
    $user_to_delete = $_GET['id'];
    $current_admin = $_SESSION['user_id'];

    // Prevent Admin from deleting themselves
    if ($user_to_delete == $current_admin) {
        header("Location: ../pages/manage_users.php?error=self_delete");
        exit();
    }

    try {
        // Prepare deletion statement
        // Note: CASCADE constraints in your DB will handle providers, reviews, etc.
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $result = $stmt->execute([$user_to_delete]);

        if ($result) {
            header("Location: ../pages/manage_users.php?success=deleted");
        } else {
            header("Location: ../pages/manage_users.php?error=failed");
        }
    } catch (PDOException $e) {
        // Handle database errors (e.g., integrity constraints not covered by cascade)
        header("Location: ../pages/manage_users.php?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: ../pages/manage_users.php");
}
exit();