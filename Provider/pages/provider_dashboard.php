<?php
session_start();
include "../../includes/config.php";

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../Reg/signin/pages/signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User & Provider Data
$user_query = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
$user_query->execute([$user_id]);
$user_data = $user_query->fetch();
$user_display_name = $user_data['full_name'] ?? "Provider";

$stmt = $pdo->prepare("SELECT provider_id, base_service, jobs_completed, pending_commission FROM providers WHERE user_id = ?");
$stmt->execute([$user_id]);
$provider = $stmt->fetch();

if (!$provider) {
    die("Access Denied: Provider profile not found.");
}

$provider_id = $provider['provider_id'];
$specialty = $provider['base_service'];
$pending_debt = $provider['pending_commission'] ?? 0.00;
$completed_count = $provider['jobs_completed'] ?? 0;

// 3. Fetch Statistics
$open_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE (service_type = ? OR target_provider_id = ?) AND status = 'open'");
$open_count_stmt->execute([$specialty, $provider_id]);
$available_count = $open_count_stmt->fetchColumn();

// 4. Fetch Available Jobs
$stmt = $pdo->prepare("
    SELECT 
        j.job_id, j.service_type, j.title, j.description, j.urgency_level, 
        j.created_at, j.location_address, j.location_pin, j.file_path,
        u.full_name AS client_name 
    FROM jobs j
    JOIN users u ON j.user_id = u.user_id
    WHERE (j.service_type = ? OR j.target_provider_id = ?)
      AND j.status = 'open'
    ORDER BY j.created_at DESC
");
$stmt->execute([$specialty, $provider_id]);
$jobs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= htmlspecialchars($user_display_name) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/provider_dashboard.css">

</head>

<body>
<?php include "../includes/provider_nav.php"; ?>

    <div class="container">
        <header class="row align-items-end mb-5" data-aos="fade-up">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-2 rounded-pill">Welcome back, Expert</span>
                <h1 class="display-5 fw-bold mb-0">Hello, <?= explode(' ', $user_display_name)[0] ?></h1>
                <p class="text-muted fs-5">You're viewing available requests for <strong class="text-dark"><?= htmlspecialchars($specialty) ?></strong></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="bg-white p-3 rounded-4 border d-inline-block text-start shadow-sm">
                    <small class="text-muted d-block fw-medium mb-1">Platform Commission Balance</small>
                    <h4 class="mb-0 fw-bold <?= $pending_debt > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($pending_debt, 2) ?> JOD
                    </h4>
                </div>
            </div>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-group-card">
                    <div class="icon-shape bg-primary-subtle text-primary"><i class="bi bi-briefcase"></i></div>
                    <h3 class="fw-bold mb-1"><?= $available_count ?></h3>
                    <p class="text-muted mb-0 small fw-medium">Available Missions</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-group-card">
                    <div class="icon-shape bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></div>
                    <h3 class="fw-bold mb-1"><?= $completed_count ?></h3>
                    <p class="text-muted mb-0 small fw-medium">Successfully Completed</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-group-card">
                    <div class="icon-shape bg-warning-subtle text-warning"><i class="bi bi-star"></i></div>
                    <h3 class="fw-bold mb-1">Active</h3>
                    <p class="text-muted mb-0 small fw-medium">Provider Standing</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center mb-4">
            <h4 class="fw-bold mb-0">Marketplace</h4>
            <hr class="flex-grow-1 ms-4 opacity-10">
        </div>

        <div class="row g-4">
            <?php if (empty($jobs)): ?>
                <div class="col-12 text-center py-5">
                    <h5 class="text-muted">No missions available right now</h5>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="col-lg-4 col-md-6" data-aos="zoom-in">
                        <div class="mission-card">
                            <div class="mb-3 d-flex justify-content-between align-items-start">
                                <span class="small fw-bold text-muted text-uppercase">
                                    <span class="urgency-dot <?= $job['urgency_level'] === 'high' ? 'bg-danger' : 'bg-primary' ?>"></span>
                                    <?= $job['urgency_level'] ?> Priority
                                </span>
                                <span class="small text-muted"><?= date('M d', strtotime($job['created_at'])) ?></span>
                            </div>

                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($job['title'] ?: $job['service_type']) ?></h5>
                            <p class="text-muted small mb-4 flex-grow-1">
                                <?= mb_strimwidth(htmlspecialchars($job['description']), 0, 100, "...") ?>
                            </p>

                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-geo-alt text-danger me-2"></i>
                                    <span class="small text-dark fw-medium text-truncate"><?= htmlspecialchars($job['location_address']) ?></span>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button onclick='viewDetails(<?= json_encode($job) ?>)' class="btn btn-action">
                                    View Details & Bid
                                </button>
                                
                                <?php if (!empty($job['location_pin'])): ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($job['location_pin']) ?>" 
                                       target="_blank" class="btn btn-outline-success btn-sm rounded-3">
                                        <i class="bi bi-map-fill me-1"></i> Client Location
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init({ duration: 600, once: true });

        function viewDetails(job) {
            Swal.fire({
                title: `<div class="mt-2 fw-bold">${job.title || job.service_type}</div>`,
                width: '550px',
                html: `
                    <div class="text-start mt-3">
                        <div class="bg-light p-3 rounded-4 mb-3">
                            <label class="text-muted small d-block mb-1">Description</label>
                            <div class="text-dark small" style="white-space: pre-line;">${job.description}</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="text-muted small d-block">Client</label>
                                <span class="fw-semibold small">${job.client_name}</span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block">Address</label>
                                <span class="fw-semibold small">${job.location_address}</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            ${job.location_pin ? `
                                <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(job.location_pin)}" target="_blank" class="btn btn-outline-primary btn-sm flex-grow-1 rounded-3">
                                    <i class="bi bi-geo-alt-fill me-1"></i> Open Map
                                </a>
                            ` : ''}
                            ${job.file_path ? `
                                <button onclick="showImage('../../${job.file_path}')" class="btn btn-outline-secondary btn-sm flex-grow-1 rounded-3">
                                    <i class="bi bi-image me-1"></i> View Attachment
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Submit Quotation',
                cancelButtonText: 'Close',
                confirmButtonColor: '#4f46e5'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitOffer(job.job_id, <?= $provider_id ?>);
                }
            });
        }

        // Function to show the image inside a SweetAlert instead of a new page
        function showImage(path) {
            Swal.fire({
                imageUrl: path,
                imageAlt: 'Job Attachment',
                width: 'auto',
                padding: '10px',
                showCloseButton: true,
                showConfirmButton: false
            });
        }

        function submitOffer(jobId, providerId) {
            Swal.fire({
                title: 'Your Price Proposal',
                input: 'number',
                inputPlaceholder: 'Amount in JOD',
                showCancelButton: true,
                confirmButtonText: 'Submit Offer',
                confirmButtonColor: '#4f46e5',
                preConfirm: (price) => {
                    if (!price || price <= 0) return Swal.showValidationMessage('Enter a valid amount');
                    return { price };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('job_id', jobId);
                    fd.append('provider_id', providerId);
                    fd.append('price', result.value.price);
                    fd.append('message', '');

                    fetch('../actions/submit_offer.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            Swal.fire('Success!', 'Your offer was sent.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', d.message || 'Error sending offer.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Network error.', 'error'));
                }
            });
        }
    </script>
</body>
</html>