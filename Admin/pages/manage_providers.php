<?php
include '../includes/header.php';

// Updated Query: Joins users and calculates average rating from reviews table
$query = "
    SELECT 
        p.*, 
        u.full_name, 
        u.email,
        (SELECT AVG(rating) FROM reviews r WHERE r.provider_id = p.provider_id) as avg_rating
    FROM providers p 
    JOIN users u ON p.user_id = u.user_id
";
$providers = $pdo->query($query)->fetchAll();
?>

<link rel="stylesheet" href="../assets/css/providers_style.css">

<div class="container-fluid px-4 py-5">
    <div class="header-section mb-5" data-aos="fade-down">
        <h2 class="fw-bold text-dark">Provider Management</h2>
        <p class="text-muted">Review, monitor, and manage your platform's service experts.</p>
    </div>

    <div class="row">
        <?php foreach ($providers as $pro):
            $rating = number_format($pro['avg_rating'] ?? 0, 1);
            $image = !empty($pro['image']) ? $pro['image'] : '../../assets/images/default-avatar.png';
        ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="modern-provider-card" data-aos="fade-up">
                    <div class="card-status-bar">
                        <span class="badge-status online">Active</span>
                        <button class="btn-more-options" onclick="deleteUser(<?= $pro['user_id'] ?>)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <div class="image-container">
                            <img src="<?= $image ?>" class="profile-img" alt="Provider Image">
                        </div>
                        <h5 class="mt-3 mb-1 fw-bold"><?= htmlspecialchars($pro['full_name']) ?></h5>
                        <small class="text-primary fw-medium"><?= htmlspecialchars($pro['base_service'] ?: 'Specialist') ?></small>
                    </div>

                    <div class="stats-grid mt-4">
                        <div class="stat-box">
                            <span class="stat-label">Rating</span>
                            <span class="stat-value text-warning">
                                <i class="fas fa-star me-1"></i><?= $rating ?>
                            </span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Jobs</span>
                            <span class="stat-value"><?= $pro['jobs_completed'] ?></span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Hourly</span>
                            <span class="stat-value">$<?= number_format($pro['hourly_rate'], 0) ?></span>
                        </div>
                    </div>

                    <div class="card-actions mt-4">
                        <a href="../../User/pages/provider_profile_page.php?id=<?= $pro['provider_id'] ?>" class="btn btn-primary-modern w-100 mb-2">
                            View Portfolio
                        </a>
                        <a href="provider_history.php?id=<?= $pro['provider_id'] ?>" class="btn btn-outline-modern w-100">
                            <i class="fas fa-history me-1"></i> View History
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>