<?php include '../includes/header.php'; 
$job_id = $_GET['id'] ?? 0;

// Fetch job with user and assigned provider info
$stmt = $pdo->prepare("SELECT j.*, u.full_name as client_name, p_u.full_name as provider_name 
                       FROM jobs j 
                       JOIN users u ON j.user_id = u.user_id
                       LEFT JOIN providers p ON j.target_provider_id = p.provider_id
                       LEFT JOIN users p_u ON p.user_id = p_u.user_id
                       WHERE j.job_id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();
?>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mt-4 mb-3">
        <a href="manage_jobs.php" class="btn btn-light me-3"><i class="fas fa-arrow-left"></i></a>
        <h2 class="mb-0">Job Detail: #<?= $job_id ?></h2>
    </div>

    <div class="row">
        <div class="col-lg-8" data-aos="fade-right">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
                        <h5 class="fw-bold text-primary"><?= $job['title'] ?></h5>
                        <span class="badge bg-dark"><?= strtoupper($job['status']) ?></span>
                    </div>
                    <p class="text-muted"><?= nl2br($job['description']) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-6">
                            <label class="small text-muted d-block">LOCATION</label>
                            <p><i class="fas fa-map-marker-alt text-danger me-2"></i><?= $job['location_address'] ?></p>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block">PAYMENT METHOD</label>
                            <p><i class="fab fa-cc-paypal text-info me-2"></i><?= strtoupper($job['payment_method']) ?></p>
                        </div>
                    </div>

                    <?php if($job['file_path']): ?>
                    <div class="mt-3">
                        <label class="small text-muted d-block mb-2">ATTACHMENT</label>
                        <img src="../../<?= $job['file_path'] ?>" class="img-fluid rounded border" style="max-height: 300px;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h6 class="mb-0">Offers Received</h6></div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-4">Provider</th>
                                <th>Quote</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $apps = $pdo->prepare("SELECT ja.*, u.full_name FROM job_applications ja JOIN providers p ON ja.provider_id = p.provider_id JOIN users u ON p.user_id = u.user_id WHERE ja.job_id = ?");
                            $apps->execute([$job_id]);
                            while($app = $apps->fetch()): ?>
                            <tr>
                                <td class="ps-4"><?= $app['full_name'] ?></td>
                                <td class="fw-bold text-success"><?= $app['quote_amount'] ?> JOD</td>
                                <td><span class="badge bg-secondary"><?= $app['application_status'] ?></span></td>
                                <td class="small"><?= $app['applied_at'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-left">
            <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
                <div class="card-body">
                    <h6>Client Information</h6>
                    <hr class="border-white opacity-25">
                    <p class="mb-1"><i class="fas fa-user me-2"></i> <?= $job['client_name'] ?></p>
                    <p class="mb-0 small opacity-75">Posted on: <?= $job['created_at'] ?></p>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Financial Snapshot</h6>
                    <div class="d-flex justify-content-between mt-3">
                        <span class="text-muted">Platform Commission:</span>
                        <span class="fw-bold text-success"><?= $job['commission_amount'] ?> JOD</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2 border-top pt-2">
                        <span class="text-muted">Payout Status:</span>
                        <span class="badge bg-light text-dark"><?= strtoupper($job['payout_status']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>