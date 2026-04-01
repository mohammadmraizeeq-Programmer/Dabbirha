<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    try {
        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $role, $hashed_password, $user_id]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $role, $user_id]);
        }

        header("Location: ../pages/manage_users.php?success=updated");
    } catch (PDOException $e) {
        header("Location: ../pages/manage_users.php?error=" . urlencode($e->getMessage()));
    }
}