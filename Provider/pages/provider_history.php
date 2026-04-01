<?php
session_start();
include "../../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../reg.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get provider details
$stmt = $pdo->prepare("SELECT provider_id FROM providers WHERE user_id = ?");
$stmt->execute([$user_id]);
$provider = $stmt->fetch();

if (!$provider) {
    die("Access Denied: Provider profile not found.");
}

$provider_id = $provider['provider_id'];

// Fetch finished jobs
$sql = "
    SELECT 
        j.*,
        r.rating,
        r.review_text,
        u.full_name AS client_name,
        u.phone AS client_phone
    FROM jobs j
    JOIN job_applications ja ON j.job_id = ja.job_id
    LEFT JOIN reviews r ON j.job_id = r.job_id
    JOIN users u ON j.user_id = u.user_id
    WHERE ja.provider_id = ?
      AND j.status IN ('completed','closed')
      AND ja.application_status = 'accepted'
    ORDER BY j.updated_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$provider_id]);
$history = $stmt->fetchAll();

// Helper: rating stars
function renderStars($rating)
{
    if (!$rating) return '<span class="text-muted small">Pending Rating</span>';
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= ($i <= $rating) ? '⭐' : '☆';
    }
    return $out;
}

// Helper: payment badge
function paymentBadge($method)
{
    if ($method === 'cash') {
        return '<span class="badge bg-warning text-dark"><i class="fas fa-money-bill-wave me-1"></i> Cash</span>';
    }
    return '<span class="badge bg-success"><i class="fas fa-credit-card me-1"></i> Online (Visa)</span>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Work History | Dabberha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f4f7f6;
            font-family: 'Poppins', sans-serif;
        }

        .history-card {
            border: none;
            border-radius: 18px;
            background: #fff;
            transition: .3s;
        }

        .history-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, .08);
        }

        .status-badge {
            font-size: .7rem;
            padding: 6px 14px;
            border-radius: 30px;
            text-transform: uppercase;
        }

        .client-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-row {
            background: #f8fafc;
            border-radius: 12px;
            padding: 10px 12px;
        }

        .empty-state {
            padding: 100px 20px;
            text-align: center;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="provider_dashboard.php">
                <i class="fas fa-tools me-2"></i>Dabberha Provider
            </a>
        </div>
    </nav>

    <div class="container pb-5">

        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <h2 class="fw-bold">Work History</h2>
            <span class="badge bg-secondary rounded-pill"><?php echo count($history); ?> Jobs</span>
        </div>

        <?php if (empty($history)): ?>
            <div class="empty-state bg-white rounded shadow-sm">
                <h4>No completed jobs yet</h4>
                <p class="text-muted">Finished jobs will appear here.</p>
            </div>
        <?php else: ?>

            <div class="row g-4">
                <?php foreach ($history as $i => $job): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $i * 50; ?>">
                        <div class="card history-card h-100 p-3">

                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="status-badge <?php echo $job['status'] === 'closed' ? 'bg-success' : 'bg-info'; ?>">
                                    <?php echo $job['status']; ?>
                                </span>
                                <small class="text-muted">
                                    <i class="far fa-calendar me-1"></i>
                                    <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                </small>
                            </div>

                            <h5 class="fw-bold text-truncate"><?php echo htmlspecialchars($job['service_type']); ?></h5>
                            <p class="text-muted small mb-3"><?php echo htmlspecialchars(substr($job['description'], 0, 80)); ?>...</p>

                            <div class="payment-row d-flex justify-content-between align-items-center mb-3">
                                <small class="fw-bold text-muted">Payment</small>
                                <?php echo paymentBadge($job['payment_method']); ?>
                            </div>

                            <div class="d-flex align-items-center border-top pt-3">
                                <div class="client-avatar me-3">
                                    <?php echo strtoupper($job['client_name'][0]); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($job['client_name']); ?></h6>
                                    <div class="text-warning small">
                                        <?php echo renderStars($job['rating']); ?>
                                    </div>
                                </div>
                                <button onclick='viewDetails(<?php echo json_encode($job); ?>)'
                                    class="btn btn-outline-primary btn-sm rounded-circle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        AOS.init({
            once: true
        });

        function viewDetails(job) {
            Swal.fire({
                title: job.service_type,
                html: `
            <div class="text-start">
                <p><strong>Client:</strong> ${job.client_name}</p>
                <p><strong>Payment Method:</strong>
                    ${job.payment_method === 'cash'
                        ? '<span class="badge bg-warning text-dark">Cash</span>'
                        : '<span class="badge bg-success">Online</span>'}
                </p>
                <hr>
                <p>${job.description}</p>
                <h6 class="mt-3">Rating</h6>
                <div class="fs-4 text-warning">
                    ${'⭐'.repeat(job.rating || 0)}${'☆'.repeat(5-(job.rating||0))}
                </div>
                ${job.review_text
                    ? `<div class="mt-2 p-2 bg-light rounded">"${job.review_text}"</div>`
                    : `<p class="text-muted small mt-2">No written review</p>`}
            </div>
        `,
                confirmButtonText: 'Close',
                confirmButtonColor: '#0d6efd',
                showCloseButton: true
            });
        }
    </script>

</body>

</html>