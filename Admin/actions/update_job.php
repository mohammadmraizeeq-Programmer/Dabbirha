<?php
require_once '../../includes/config.php';
$id = $_GET['id'] ?? null;
if($id) {
    // Cascading delete is handled by your DB constraints (ON DELETE CASCADE)
    $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$id]);
}
header("Location: ../pages/manage_users.php");