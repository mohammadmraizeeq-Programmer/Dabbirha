<?php
session_start();
require_once '../../includes/config.php';

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$job_id = $_GET['id'] ?? 0;

if (!$job_id) {
    header("Location: dashboard.php");
    exit;
}

// 2. Fetch User Details (THIS FIXES THE NAVBAR ERROR)
$user_query = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
$user_query->execute([$user_id]);
$user_data = $user_query->fetch(PDO::FETCH_ASSOC);
$user_display_name = $user_data['full_name'] ?? "User";

// 3. Fetch Job and Provider Details
$sql = "SELECT j.*, u.full_name as provider_name, u.phone as provider_phone, 
               ja.quote_amount, ja.application_id, p.provider_id
        FROM jobs j
        LEFT JOIN job_applications ja ON j.job_id = ja.job_id AND ja.application_status IN ('pending', 'accepted', 'assigned')
        LEFT JOIN providers p ON ja.provider_id = p.provider_id
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE j.job_id = ? AND j.user_id = ?
        ORDER BY ja.application_status DESC LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$job_id, $user_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "Access denied or job not found.";
    exit;
}

// Currency conversion for PayPal
$conversion_rate = 1.41;
$paypal_amount = number_format(($job['quote_amount'] ?? 0) * $conversion_rate, 2, '.', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission Control | Dabberha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/job_details.css">
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <header class="mission-header" style="background: linear-gradient(135deg, #001a16 0%, #008770 100%);">
        <div class="container">
            <a href="dashboard.php" class="back-btn mb-4 d-inline-block">
                <i class="bi bi-arrow-left-circle-fill me-2"></i> Return to Console
            </a>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end">
                <div>
                    <h1 class="display-3 mb-2 text-white"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <p class="opacity-75 mb-0 text-white">MISSION ID: #<?php echo $job['job_id']; ?> • SYSTEM STATUS: ONLINE</p>
                </div>
                <div class="text-md-end mt-3 mt-md-0">
                    <span class="status-capsule bg-white text-dark shadow-sm">
                        <i class="bi bi-broadcast me-2"></i><?php echo strtoupper($job['status']); ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card p-5">
                    <h3 class="mb-4 text-success"><i class="bi bi-info-square me-2"></i>Project Specifications</h3>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 border">
                                <label class="text-muted small text-uppercase fw-800">Specialty Required</label>
                                <p class="h5 mb-0 mt-1"><?php echo ucwords(str_replace('_', ' ', $job['service_type'])); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 border">
                                <label class="text-muted small text-uppercase fw-800">Budgeted Amount</label>
                                <p class="price-tag mb-0">JOD <?php echo number_format($job['quote_amount'], 2); ?></p>
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-3">Service Description</h4>
                    <p class="lead text-muted" style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card p-4 mb-4">
                    <h5 class="fw-800 mb-4 border-bottom pb-2">ASSIGNED EXPERT</h5>
                    <div class="provider-profile-mini">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($job['provider_name']); ?>&background=008770&color=fff" class="rounded-circle me-3 border border-3 border-success" width="65">
                            <div>
                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($job['provider_name']); ?></h6>
                                <p class="small text-muted mb-2">Primary Provider</p>
                                <a href="tel:<?php echo $job['provider_phone']; ?>" class="btn btn-sm btn-dark rounded-pill px-3">
                                    <i class="bi bi-telephone-fill me-1"></i> CALL NOW
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <h5 class="fw-800 mb-4 border-bottom pb-2">COMMAND CENTER</h5>
                    <?php if ($job['status'] === 'open'): ?>
                        <div class="payment-selection-group mb-4">
                            <div class="form-check payment-selection-box mb-3">
                                <input class="form-check-input" type="radio" name="pay_method" id="pay_cash" checked>
                                <label class="form-check-label fw-bold w-100" for="pay_cash">
                                    <i class="bi bi-cash-coin me-2"></i> Cash Transaction
                                </label>
                            </div>
                            <div class="form-check payment-selection-box">
                                <input class="form-check-input" type="radio" name="pay_method" id="pay_paypal">
                                <label class="form-check-label fw-bold w-100" for="pay_paypal">
                                    <i class="bi bi-paypal me-2"></i> Digital Transfer
                                </label>
                            </div>
                        </div>
                        <button id="confirm-cash-btn" class="btn btn-primary-modern btn-modern w-100 py-3">
                            INITIALIZE HIRING
                        </button>
                        <div id="paypal-button-container" class="mt-3"></div>

                    <?php elseif ($job['status'] === 'in_progress' || $job['status'] === 'completed'): ?>
                        <div class="alert alert-success border-0 rounded-4 mb-4">
                            <i class="bi bi-shield-check me-2"></i> Service in progress or pending finalization.
                        </div>
                        <button onclick="completeJob(<?php echo $job['job_id']; ?>)" class="btn btn-primary-modern btn-modern w-100 py-3">
                            RELEASE FUNDS & CLOSE
                        </button>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-archive text-muted display-4"></i>
                            <p class="text-muted mt-2">This mission is currently <?php echo $job['status']; ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="job-data" 
         data-job-id="<?php echo $job['job_id']; ?>" 
         data-app-id="<?php echo $job['application_id']; ?>" 
         data-paypal-amount="<?php echo $paypal_amount; ?>"></div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($job['status'] === 'open'): ?>
        <script src="https://www.paypal.com/sdk/js?client-id=AX3fbpGOLL30XZ5OzpswMb8_aque2k2XpX23Q-9YN-ZXyyMAYXQqfTJW-27Sxh1IJ0G8rna-rsNzxoU7&currency=USD"></script>
    <?php endif; ?>
    <script src="../assets/js/job_details.js"></script>
</body>
</html>