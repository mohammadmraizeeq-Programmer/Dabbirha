<?php 
include '../includes/header.php'; 

if (!isset($_GET['id'])) {
    header("Location: manage_providers.php");
    exit();
}

$provider_id = (int)$_GET['id'];

// 1. Fetch Provider & User Info
$stmt = $pdo->prepare("
    SELECT p.*, u.full_name, u.email 
    FROM providers p 
    JOIN users u ON p.user_id = u.user_id 
    WHERE p.provider_id = ?
");
$stmt->execute([$provider_id]);
$provider = $stmt->fetch();

if (!$provider) {
    die("Provider not found.");
}

// 2. Fetch FULL Job History
// This query looks for jobs where they were the target OR where they were the selected applicant
$stmt = $pdo->prepare("
    SELECT j.*, u.full_name as client_name, ja.quote_amount as price
    FROM jobs j 
    JOIN users u ON j.user_id = u.user_id 
    LEFT JOIN job_applications ja ON j.job_id = ja.job_id AND ja.application_status = 'accepted'
    WHERE j.target_provider_id = ? 
       OR (ja.provider_id = ? AND ja.application_status = 'accepted')
    ORDER BY j.created_at DESC
");
$stmt->execute([$provider_id, $provider_id]);
$jobs = $stmt->fetchAll();

// 3. Calculate Stats (Using 'price' as per your SQL schema)
$total_earned = 0;
foreach($jobs as $j) {
    if($j['status'] == 'closed') {
        $total_earned += $j['price']; 
    }
}
?>

<div class="container-fluid px-4 py-5">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <a href="manage_providers.php" class="text-decoration-none small text-muted">
                <i class="fas fa-chevron-left"></i> Back to Providers
            </a>
            <h2 class="fw-bold mt-2"><?= htmlspecialchars($provider['full_name']) ?> <span class="text-muted small">#PH-<?= $provider_id ?></span></h2>
        </div>
        <div class="text-end">
            <span class="badge bg-soft-success text-success p-2 px-3 rounded-pill">Verified Expert</span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center bg-white">
                <div class="text-primary mb-2"><i class="fas fa-briefcase fa-2x"></i></div>
                <h4 class="fw-bold mb-0"><?= count($jobs) ?></h4>
                <small class="text-muted text-uppercase">Total Projects</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center bg-white">
                <div class="text-success mb-2"><i class="fas fa-hand-holding-usd fa-2x"></i></div>
                <h4 class="fw-bold mb-0">$<?= number_format($total_earned, 2) ?></h4>
                <small class="text-muted text-uppercase">Earnings History</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center bg-white">
                <div class="text-warning mb-2"><i class="fas fa-star fa-2x"></i></div>
                <h4 class="fw-bold mb-0"><?= number_format($provider['rating'] ?? 0, 1) ?></h4>
                <small class="text-muted text-uppercase">Average Rating</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold">Comprehensive Job Logs</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Project Title</th>
                            <th>Client</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Timeline</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <img src="../../assets/images/empty.svg" alt="" width="80" class="mb-3 opacity-25"><br>
                                    <span class="text-muted">This provider has no job history yet.</span>
                                </td>
                            </tr>
                        <?php else: foreach($jobs as $job): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($job['title']) ?></div>
                                    <span class="badge bg-soft-primary text-primary" style="font-size: 10px;">
                                        <?= strtoupper($job['service_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-medium"><?= htmlspecialchars($job['client_name']) ?></div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">$<?= number_format($job['price'], 2) ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $status_color = match($job['status']) {
                                            'open' => '#0dcaf0',
                                            'in_progress' => '#0d6efd',
                                            'closed' => '#198754',
                                            'cancelled' => '#dc3545',
                                            default => '#6c757d'
                                        };
                                    ?>
                                    <span class="badge rounded-pill" style="background-color: <?= $status_color ?>20; color: <?= $status_color ?>;">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i> <?= strtoupper($job['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($job['created_at'])) ?></small>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="job_details.php?id=<?= $job['job_id'] ?>" class="btn btn-sm btn-light border">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>