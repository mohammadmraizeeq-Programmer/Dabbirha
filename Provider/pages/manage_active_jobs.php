<?php
session_start();
require_once "../../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../reg.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Provider lookup
$stmt = $pdo->prepare("SELECT provider_id FROM providers WHERE user_id = ?");
$stmt->execute([$user_id]);
$provider = $stmt->fetch();

if (!$provider) {
    die("Access Denied: Provider profile not found.");
}

$provider_id = $provider['provider_id'];

// 1. Fetch ONLY truly active jobs for the display list
$sql = "
    SELECT 
        j.job_id,
        j.service_type,
        j.title,
        j.description,
        j.location_address,
        j.urgency_level,
        j.status,
        j.payment_method,
        j.file_path,
        u.full_name AS client_name,
        u.phone AS client_phone,
        ja.quote_amount
    FROM jobs j
    INNER JOIN job_applications ja ON j.job_id = ja.job_id
    INNER JOIN users u ON j.user_id = u.user_id
    WHERE ja.provider_id = ?
      AND ja.application_status = 'assigned'
      AND j.status IN ('assigned','in_progress')
    ORDER BY j.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$provider_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. NEW LOGIC: Fetch ONLY if a job was closed in the last 10 seconds 
// (This ensures the alert only pops up immediately after the status update)
$stmtClose = $pdo->prepare("
    SELECT j.job_id, u.full_name AS client_name, ja.quote_amount, j.payment_method
    FROM jobs j
    INNER JOIN job_applications ja ON j.job_id = ja.job_id
    INNER JOIN users u ON j.user_id = u.user_id
    WHERE ja.provider_id = ? 
      AND j.status = 'closed'
      AND j.updated_at >= NOW() - INTERVAL 10 SECOND
    ORDER BY j.job_id DESC LIMIT 1
");
$stmtClose->execute([$provider_id]);
$latestClosedJob = $stmtClose->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Active Work Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/manage_active_jobs.css">
</head>

<body>

    <?php include "../includes/provider_nav.php"; ?>

    <main class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <div>
                <h1 class="fw-bold">Active Work Orders</h1>
                <p class="text-muted mb-0">Manage your ongoing tasks</p>
            </div>
            <div class="d-flex align-items-center bg-white border rounded-4 px-3 py-2 shadow-sm">
                <span class="pulse-icon"></span>
                <span class="fw-bold text-dark me-1"><?php echo count($jobs); ?></span>
                <span class="text-muted small fw-medium">Active Jobs</span>
            </div>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <h4 class="text-muted">No active jobs</h4>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($jobs as $job):
                    $is_assigned = $job['status'] === 'assigned';
                    $is_progress = $job['status'] === 'in_progress';
                ?>
                    <div class="col-lg-6" data-aos="fade-up">
                        <div class="card job-card p-4 h-100">

                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <span class="fw-bold text-primary"><?php echo htmlspecialchars($job['service_type']); ?></span>
                                    <h5 class="fw-bold mt-1"><?php echo htmlspecialchars($job['title'] ?: 'Service Job'); ?></h5>
                                </div>
                                <div class="fw-bold text-success">
                                    <?php echo number_format($job['quote_amount'], 2); ?> JOD
                                </div>
                            </div>

                            <div class="stepper">
                                <div class="step active"><i class="bi bi-check"></i></div>
                                <div class="step <?php echo $is_progress ? 'active' : ''; ?>">
                                    <?php echo $is_progress ? '<i class="bi bi-play-fill"></i>' : '2'; ?>
                                </div>
                                <div class="step">
                                    <i class="bi bi-flag-fill"></i>
                                </div>
                            </div>

                            <div class="description-box mb-3">
                                <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                            </div>

                            <div class="client-info mb-3">
                                <strong><?php echo htmlspecialchars($job['client_name']); ?></strong><br>
                                <a href="tel:<?php echo $job['client_phone']; ?>" class="fw-bold text-decoration-none">
                                    <i class="bi bi-telephone"></i> <?php echo $job['client_phone']; ?>
                                </a>
                            </div>

                            <div class="mt-3 p-3 border rounded bg-light">
                                <h6 class="fw-bold"><i class="bi bi-wallet2 me-2"></i>Payment Status</h6>
                                <div class="text-muted">
                                    <i class="bi bi-lock-fill"></i> Details will be revealed after completion.
                                </div>
                            </div>

                            <?php if ($is_assigned): ?>
                                <button onclick="updateStatus(<?php echo $job['job_id']; ?>,'in_progress')" class="btn btn-primary w-100 mt-3">
                                    <i class="bi bi-play-fill"></i> Start Work
                                </button>
                            <?php elseif ($is_progress): ?>
                                <div class="alert alert-info text-center mt-3">
                                    <i class="bi bi-clock-history"></i> Work in progress
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init();

        async function updateStatus(jobId, status) {
            const c = await Swal.fire({
                title: 'Start job?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Start'
            });
            if (!c.isConfirmed) return;

            const fd = new FormData();
            fd.append('job_id', jobId);
            fd.append('status', status);

            const r = await fetch('../actions/update_job_status.php', {
                method: 'POST',
                body: fd
            });
            const d = await r.json();

            if (d.success) {
                Swal.fire('Started', d.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', d.message, 'error');
            }
        }

        // Updated Alert Logic to show specific instructions based on payment method
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($latestClosedJob): ?>
                Swal.fire({
                    title: 'Excellent Work!',
                    html: `
                        <div class="text-center mb-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Job Successfully Completed</h5>
                        </div>
                        <div class="text-start p-3 bg-light rounded border">
                            <p class="mb-1"><strong>Client:</strong> <?php echo htmlspecialchars($latestClosedJob['client_name']); ?></p>
                            <p class="mb-1"><strong>Amount:</strong> <?php echo number_format($latestClosedJob['quote_amount'], 2); ?> JOD</p>
                            <hr>
                            <?php if ($latestClosedJob['payment_method'] === 'cash'): ?>
                                <div class="alert alert-warning border-warning d-flex align-items-center mb-0">
                                    <i class="bi bi-cash-stack me-2 fs-4"></i>
                                    <div>
                                        <strong>CASH PAYMENT:</strong><br>
                                        Please collect <b><?php echo number_format($latestClosedJob['quote_amount'], 2); ?> JOD</b> from the client now.
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-primary border-primary d-flex align-items-center mb-0">
                                    <i class="bi bi-credit-card me-2 fs-4"></i>
                                    <div>
                                        <strong>ELECTRONIC PAYMENT:</strong><br>
                                        The amount has been paid online. It will be added to your digital wallet.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    `,
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#0d6efd'
                });
            <?php endif; ?>
        });
    </script>

</body>
</html>