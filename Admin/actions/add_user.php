<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        
        // If provider, create entry in providers table too
        if($role == 'provider'){
            $last_id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO providers (user_id) VALUES (?)")->execute([$last_id]);
        }

        header("Location: ../pages/manage_users.php?success=1");
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}