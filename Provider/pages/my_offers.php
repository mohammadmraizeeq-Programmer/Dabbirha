<?php
session_start();
include "../../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../reg.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT provider_id FROM providers WHERE user_id = ?");
$stmt->execute([$user_id]);
$provider = $stmt->fetch();
$provider_id = $provider['provider_id'];

$sql = "
    SELECT ja.*, j.title, j.service_type, u.full_name as client_name 
    FROM job_applications ja
    JOIN jobs j ON ja.job_id = j.job_id
    JOIN users u ON j.user_id = u.user_id
    WHERE ja.provider_id = ?
    ORDER BY ja.applied_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$provider_id]);
$offers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .offer-card { border: none; border-radius: 15px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.75rem; padding: 5px 12px; border-radius: 50px; text-transform: uppercase; }
        .badge-assigned { background: #dcfce7; color: #166534; border: 1px solid #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body class="bg-light p-4">
    <?php include "../includes/provider_nav.php"; ?>
<div class="container">
    <h2 class="fw-bold mb-4" data-aos="fade-right">My offers</h2>
    <div class="row g-3">
        <?php foreach($offers as $o): ?>
        <div class="col-12" data-aos="fade-up">
            <div class="offer-card p-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($o['title'] ?: $o['service_type']); ?></h6>
                    <small class="text-muted">Client: <?php echo htmlspecialchars($o['client_name']); ?> | Sent: <?php echo date('M d', strtotime($o['applied_at'])); ?></small>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-primary mb-1">$<?php echo $o['quote_amount']; ?></div>
                    <span class="status-badge badge-<?php echo $o['application_status']; ?>">
                        <?php echo $o['application_status']; ?>
                    </span>
                    <?php if($o['application_status'] === 'assigned'): ?>
                        <a href="manage_active_jobs.php" class="btn btn-sm btn-dark ms-2 rounded-pill">Manage</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>