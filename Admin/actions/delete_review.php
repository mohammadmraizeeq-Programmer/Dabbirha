<?php
require_once '../../includes/config.php';

if (isset($_GET['id'])) {
    $review_id = $_GET['id'];

    // Delete the review
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
    
    if($stmt->execute([$review_id])) {
        // Redirect back with success message
        header("Location: ../pages/manage_reviews.php?deleted=1");
    } else {
        header("Location: ../pages/manage_reviews.php?error=1");
    }
}