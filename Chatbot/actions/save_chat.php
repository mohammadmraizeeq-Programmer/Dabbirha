<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../includes/config.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Get the 'referer' (the page the user is actually on)
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // 2. Logic: If the user is on the landing page (index.php), 
    // we assume they are a "Guest" even if a session exists.
    $is_welcome_page = (strpos($referer, 'index.php') !== false || $referer == 'http://localhost/Dabbirha/');

    if ($is_welcome_page) {
        $user_id = null; // Force null for the welcome page
    } else {
        $user_id = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : null;
    }
    
    $query = $_POST['query'] ?? '';
    $response = $_POST['response'] ?? '';

    if (!empty($query)) {
        try {
            $sql = "INSERT INTO chatbot_logs (user_id, user_query, bot_response) VALUES (:u, :q, :r)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':u' => $user_id,
                ':q' => $query,
                ':r' => $response
            ]);
            
            echo json_encode(['status' => 'success', 'assigned_id' => $user_id, 'source' => $is_welcome_page ? 'Guest' : 'User']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}