<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    // If not logged in, go back to signin.php
    // Path: Out of pages -> out of User -> into Reg/signin/pages/
    header("Location: ../../Reg/signin/pages/signin.php");
    exit();
}
// 2. Database Connection
require_once "../../includes/config.php";

if (!isset($pdo)) {
    die("Database connection failed: \$pdo variable not found. Check your config.php file.");
}

$user_id = $_SESSION['user_id'];

// 3. Fetch User Details
$user_query = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
$user_query->execute([$user_id]);
$user_data = $user_query->fetch(PDO::FETCH_ASSOC);
$user_display_name = $user_data['full_name'] ?? "User";

// 4. Stats for the Neural Card
$stmt_active = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE user_id = ? AND status != 'completed'");
$stmt_active->execute([$user_id]);
$active_count = $stmt_active->fetchColumn();

$stmt_offers_count = $pdo->prepare("
    SELECT COUNT(*) FROM job_applications ja 
    JOIN jobs j ON ja.job_id = j.job_id 
    WHERE j.user_id = ? AND ja.application_status = 'pending'
");
$stmt_offers_count->execute([$user_id]);
$new_offers_count = $stmt_offers_count->fetchColumn();

// --- FETCH ALL JOBS FOR HISTORY ---
$jobs_query = $pdo->prepare("SELECT * FROM jobs WHERE user_id = ? ORDER BY created_at DESC");
$jobs_query->execute([$user_id]);
$all_jobs = $jobs_query->fetchAll(PDO::FETCH_ASSOC);

// Define the absolute latest job to show in the Command Status section
$latest_job = !empty($all_jobs) ? $all_jobs[0] : null;

// 5. Fetch Incoming Offers List
$offers_sql = "
    SELECT ja.application_id, ja.job_id, ja.quote_amount, ja.application_status as app_status, 
           j.service_type, u.full_name as provider_name, j.status as job_status
    FROM job_applications ja
    JOIN jobs j ON ja.job_id = j.job_id
    JOIN providers p ON ja.provider_id = p.provider_id
    JOIN users u ON p.user_id = u.user_id
    WHERE j.user_id = ? 
      AND ja.application_status IN ('pending', 'assigned') 
      AND j.status != 'closed'
    ORDER BY ja.applied_at DESC
";
$offers_stmt = $pdo->prepare($offers_sql);
$offers_stmt->execute([$user_id]);
$offers = $offers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dabbirha | Command Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../assets/css/dashboard.css">

</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="console-stage">
        <div class="sticky-viewport">
            <div class="flipper-card" id="mainFlipper">
                <div class="card-face face-front">
                    <div class="bg-black d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <i class="bi bi-cpu-fill display-1 text-primary mb-3"></i>
                            <h2 class="fw-800">Neural Engine</h2>
                        </div>
                    </div>
                    <div class="p-5 d-flex flex-column justify-content-center">
                        <h1 class="display-5 fw-800 text-dark" style="font-family:'Space Grotesk'">AI Diagnosis</h1>
                        <p class="text-muted mb-4">Upload a photo or video. Our Neural Engine will identify the fault and broadcast to specialists instantly.</p>
                        <div class="card-stats-grid">
                            <div class="stat-box">
                                <h4><?php echo $active_count; ?></h4><span>Active Requests</span>
                            </div>
                            <div class="stat-box">
                                <h4><?php echo $new_offers_count; ?></h4><span>New Offers</span>
                            </div>
                        </div>
                        <a href="upload_problem.php" class="btn btn-dark w-100 py-3 rounded-pill fw-bold">INITIALIZE SCAN</a>
                    </div>
                </div>

                <div class="card-face face-back">
                    <div class="p-5 text-center d-flex flex-column align-items-center justify-content-center">
                        <div class="bg-light p-4 rounded-circle mb-4"><i class="bi bi-geo-alt-fill display-5 text-primary"></i></div>
                        <h2 class="fw-800 text-dark">Provider Radar</h2>
                    </div>
                    <div class="bg-light p-5 d-flex align-items-center">
                        <button id="launchRadar" class="btn btn-primary w-100 py-3 rounded-pill text-white fw-bold" style="background:var(--primary); border:none;">BROWSE PROVIDERS</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="command-center">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5" data-aos="fade-up">
                    <h3 class="fw-800 mb-4 text-dark">Current Mission Status</h3>
                    <div class="status-line">
                        <?php if ($latest_job):
                            $status = strtolower($latest_job['status']);
                            $has_provider = !empty($latest_job['provider_id']);
                        ?>
                            <div class="status-step active-step">
                                <h6 class="fw-800 mb-0">Request Broadcasted</h6>
                                <p class="small text-muted">ID #<?php echo $latest_job['job_id']; ?>: "<?php echo htmlspecialchars($latest_job['title']); ?>" is live.</p>
                            </div>

                            <div class="status-step <?php echo ($has_provider || $status != 'pending') ? 'active-step' : 'opacity-50'; ?>">
                                <h6 class="fw-800 mb-0">Specialist Connection</h6>
                                <p class="small text-muted"><?php echo ($has_provider) ? "Provider hired and confirmed." : "Reviewing incoming bids from specialists..."; ?></p>
                            </div>

                            <div class="status-step <?php echo ($status == 'in_progress' || $status == 'completed') ? 'active-step' : 'opacity-50'; ?>">
                                <h6 class="fw-800 mb-0">Maintenance Execution</h6>
                                <p class="small text-muted">Current phase: <span class="badge bg-dark"><?php echo strtoupper($status); ?></span></p>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">
                                <p>No active missions. Start by initializing a scan.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-800 mt-5 mb-3">Service History</h5>
                    <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                        <?php if (!empty($all_jobs)): ?>
                            <?php foreach ($all_jobs as $job):
                                $badge_class = ($job['status'] == 'completed') ? 'bg-success' : (($job['status'] == 'in_progress') ? 'bg-warning text-dark' : 'bg-secondary');
                            ?>
                                <div class="history-card">
                                    <div class="me-3">
                                        <i class="bi bi-hammer fs-4 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold small"><?php echo htmlspecialchars($job['title']); ?></h6>
                                        <span class="badge badge-status <?php echo $badge_class; ?>"><?php echo $job['status']; ?></span>
                                    </div>
                                    <a href="job_details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No history found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-7" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="fw-800 mb-4 text-dark">Incoming Specialist Bids</h3>

                    <?php if (empty($offers)): ?>
                        <div class="text-center py-5 border rounded-4 bg-light text-muted">
                            <i class="bi bi-mailbox mb-3 display-6"></i>
                            <p>Standing by for specialist offers...</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($offers as $offer): ?>
                            <div class="offer-card mb-3 p-4 border-0 rounded-4 shadow-sm bg-white" id="offer-row-<?= $offer['application_id'] ?>" style="border-left: 5px solid var(--primary) !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-800 mb-1 text-dark"><?= htmlspecialchars($offer['provider_name']) ?></h6>
                                        <p class="small text-muted mb-2"><i class="bi bi-tools me-1"></i> <?= htmlspecialchars($offer['service_type']) ?></p>
                                        <div class="fs-5 fw-bold text-primary">JOD <?= number_format($offer['quote_amount'], 2) ?></div>
                                    </div>
                                    <div class="actions">
                                        <?php if ($offer['app_status'] === 'pending'): ?>
                                            <button onclick="handleOffer(<?= $offer['application_id'] ?>, 'accept', <?= $offer['job_id'] ?>)"
                                                class="btn btn-primary btn-sm rounded-pill px-4 py-2 shadow-sm">
                                                Accept Offer
                                            </button>
                                            <button onclick="handleOffer(<?= $offer['application_id'] ?>, 'decline')"
                                                class="btn btn-link text-danger btn-sm text-decoration-none ms-2">
                                                Decline
                                            </button>
                                        <?php elseif ($offer['app_status'] === 'assigned'): ?>
                                            <a href="job_details.php?id=<?= $offer['job_id'] ?>"
                                                class="btn btn-dark btn-sm rounded-pill px-4 shadow-sm">
                                                Manage Mission <i class="bi bi-arrow-right-short ms-1"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
<?php include '../includes/footer.php'; ?>
</html>

<?php 
  include_once $_SERVER['DOCUMENT_ROOT'] . '/Dabbirha/Chatbot/chatbot_widget.php'; 
?>