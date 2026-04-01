<?php
include '../includes/header.php';

// SQL Query to get jobs, clients, and the final accepted price
$query = "
    SELECT 
        j.*, 
        u.full_name AS client_name,
        u.email AS client_email,
        (SELECT ja.quote_amount FROM job_applications ja WHERE ja.job_id = j.job_id AND ja.application_status = 'accepted' LIMIT 1) as final_price
    FROM jobs j 
    JOIN users u ON j.user_id = u.user_id
    ORDER BY j.created_at DESC
";
$jobs = $pdo->query($query)->fetchAll();
?>

<link rel="stylesheet" href="../assets/css/manage_jobs.css">

<div class="container-fluid px-4 py-5 jobs-container" style="background: #f8fafc; min-height: 100vh;">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark">Marketplace Inventory</h2>
            <p class="text-muted">Currently managing <?= count($jobs) ?> total job requests across the platform.</p>
        </div>

    </div>

    <div class="row px-4 mb-3 text-muted small fw-bold text-uppercase d-none d-lg-flex">
        <div class="job-info-col">Job Information</div>
        <div class="col-client">Client</div>
        <div class="col-financials">Budget/Final</div>
        <div class="col-status">Status</div>
        <div class="col text-end">Actions</div>
    </div>

    <div class="job-list">
        <?php foreach ($jobs as $job): 
            $display_price = $job['final_price'] ?: $job['commission_amount'];
            
            // Generate slug for CSS status coloring
            $status_slug = str_replace('_', '-', $job['status']);
        ?>
        
        <div class="job-card-row p-3 shadow-sm row mx-0 align-items-center mb-3 bg-white rounded-3">
            <div class="job-info-col d-flex align-items-center">
                <div class="service-icon-box me-3">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="overflow-hidden" style="width: 100%;">
                    <h6 class="mb-0 fw-bold job-title-text text-dark" title="<?= htmlspecialchars($job['title']) ?>">
                        <?= htmlspecialchars($job['title']) ?>
                    </h6>
                    <small class="text-muted d-block"><?= htmlspecialchars($job['service_type']) ?> • #JB-<?= $job['job_id'] ?></small>
                </div>
            </div>

            <div class="col-client">
                <div class="small fw-bold text-dark"><?= htmlspecialchars($job['client_name']) ?></div>
                <div class="text-muted small truncate-email"><?= htmlspecialchars($job['client_email']) ?></div>
            </div>

            <div class="col-financials">
                <div class="price-tag fw-bold text-dark">$<?= number_format($display_price, 2) ?></div>
                <small class="text-muted" style="font-size: 10px;">Comm: $<?= number_format($job['commission_amount'], 2) ?></small>
            </div>

            <div class="col-status">
                <span class="status-pill status-<?= $status_slug ?>">
                    <?= str_replace('_', ' ', $job['status']) ?>
                </span>
            </div>

            <div class="col text-end">
                <a href="job_details.php?id=<?= $job['job_id'] ?>" class="btn btn-sm btn-outline-primary border-0 me-1" title="View Details">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteJob(<?= $job['job_id'] ?>)" title="Delete Job">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>

        <?php endforeach; ?>
    </div>
</div>

<script src="../assets/js/manage_job.js"></script>

<?php include '../includes/footer.php'; ?>