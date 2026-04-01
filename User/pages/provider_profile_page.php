<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../includes/config.php";

// --- FIX FOR NAVBAR UNDEFINED VARIABLE ---
$user_display_name = "Guest";
if (isset($_SESSION['user_id'])) {
    $stmt_nav = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $stmt_nav->bind_param("i", $_SESSION['user_id']);
    $stmt_nav->execute();
    $res_nav = $stmt_nav->get_result();
    if ($row_nav = $res_nav->fetch_assoc()) {
        $user_display_name = $row_nav['full_name'];
    }
    $stmt_nav->close();
}

if (!isset($_GET['id'])) {
    die("Provider ID not provided.");
}

$provider_id = $_GET['id'];
$provider = null;
$initials = '';
$skills = [];
$certifications = [];
$reviews = [];
$avg_rating = '0.0';
$reviews_count = 0;
$provider_phone = '';

// --- 1. FETCH MAIN PROVIDER INFO ---
$sql_provider = "
    SELECT 
        u.full_name, p.address, u.created_at, u.phone, 
        p.bio, p.base_service, p.hourly_rate, p.jobs_completed
    FROM users u
    JOIN providers p ON u.user_id = p.user_id
    WHERE p.provider_id = ? 
";

if ($stmt = $conn->prepare($sql_provider)) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $provider = $result->fetch_assoc();
        $provider_phone = htmlspecialchars($provider['phone'] ?? '');
        $name_parts = explode(" ", trim($provider['full_name']));
        $initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
    } else {
        die("Provider not found.");
    }
    $stmt->close();
}

// --- 2. FETCH SKILLS ---
$sql_skills = "SELECT skill_name FROM skills WHERE provider_id = ?";
if ($stmt = $conn->prepare($sql_skills)) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $skills[] = $row['skill_name'];
    }
    $stmt->close();
}

// --- 3. FETCH CERTIFICATIONS ---
$sql_certs = "SELECT cert_name FROM certifications WHERE provider_id = ?";
if ($stmt = $conn->prepare($sql_certs)) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $certifications[] = $row['cert_name'];
    }
    $stmt->close();
}

// --- 4. FETCH REVIEWS ---
$sql_reviews = "
    SELECT r.rating, r.review_text, r.created_at, u.full_name AS reviewer_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.provider_id = ?
    ORDER BY r.created_at DESC
";
if ($stmt = $conn->prepare($sql_reviews)) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_rating = 0;
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
        $total_rating += $row['rating'];
    }
    $reviews_count = count($reviews);
    if ($reviews_count > 0) {
        $avg_rating = number_format($total_rating / $reviews_count, 1);
    }
    $stmt->close();
}

function display_stars($rating)
{
    $html = '';
    $full_stars = floor($rating);
    $half_star = ceil($rating - $full_stars);
    for ($i = 0; $i < $full_stars; $i++) $html .= '<i class="bi bi-star-fill"></i>';
    if ($half_star) $html .= '<i class="bi bi-star-half"></i>';
    for ($i = 0; $i < (5 - $full_stars - $half_star); $i++) $html .= '<i class="bi bi-star"></i>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($provider['full_name'] ?? 'Provider'); ?> Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/provider_profile_page.css" rel="stylesheet">
</head>

<body>
    <?php include "../includes/navbar.php"; ?>
    <main class="container py-5 mt-5">
        <section class="card-modern">
            <div class="row align-items-center">
                <div class="col-md-auto text-center mb-4 mb-md-0">
                    <div class="initial-circle mx-auto"><?php echo htmlspecialchars($initials); ?></div>
                </div>
                <div class="col-md ps-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                        <div>
                            <h1 class="fw-bold mb-1"><?php echo htmlspecialchars($provider['full_name']); ?></h1>
                            <p class="text-primary fw-bold fs-5 mb-2"><?php echo htmlspecialchars($provider['base_service']); ?></p>
                        </div>
                        <div class="rating-badge">
                            <i class="bi bi-star-fill me-1"></i><?php echo $avg_rating; ?>
                        </div>
                    </div>

                    <div class="row g-3 text-muted mb-4 mt-2">
                        <div class="col-auto"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($provider['address']); ?></div>
                        <div class="col-auto"><i class="bi bi-briefcase-fill me-1"></i><?php echo $provider['jobs_completed']; ?> Jobs</div>
                        <div class="col-auto"><i class="bi bi-calendar3 me-1"></i>Joined <?php echo date("Y", strtotime($provider['created_at'])); ?></div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-4 pt-3">
                        <div>
                            <span class="rate-value"><?php echo number_format($provider['hourly_rate']); ?> JOD</span>
                            <span class="text-muted small">/ hour</span>
                        </div>
                        <div class="d-flex gap-2 ms-md-auto">
                            <a href="direct_request.php?provider_id=<?php echo $provider_id; ?>&name=<?php echo urlencode($provider['full_name']); ?>&service=<?php echo urlencode($provider['base_service']); ?>" class="btn btn-modern btn-request text-decoration-none text-center">
                                Request Service
                            </a>
                            <a href="tel:<?php echo $provider_phone; ?>" class="btn btn-modern btn-call">
                                <i class="bi bi-telephone-fill me-2"></i>Call Me
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row">
            <div class="col-lg-8">
                <div class="card-modern">
                    <h4 class="fw-bold mb-3">About</h4>
                    <p class="lh-lg"><?php echo nl2br(htmlspecialchars($provider['bio'])); ?></p>
                </div>

                <div class="card-modern">
                    <h4 class="fw-bold mb-4">Client Reviews (<?php echo $reviews_count; ?>)</h4>
                    <?php if (empty($reviews)): ?>
                        <p class="text-muted">No reviews yet.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="border-bottom pb-4 mb-4 last-child-no-border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold"><?php echo htmlspecialchars($review['reviewer_name']); ?></span>
                                    <div class="text-warning small"><?php echo display_stars($review['rating']); ?></div>
                                </div>
                                <p class="text-muted mb-1 small"><?php echo date("M d, Y", strtotime($review['created_at'])); ?></p>
                                <p class="mb-0"><?php echo htmlspecialchars($review['review_text']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-modern">
                    <h4 class="fw-bold mb-3">Skills</h4>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($skills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card-modern">
                    <h4 class="fw-bold mb-3">Certifications</h4>
                    <ul class="list-unstyled">
                        <?php foreach ($certifications as $cert): ?>
                            <li class="d-flex align-items-center mb-2">
                                <i class="bi bi-patch-check-fill text-primary me-2"></i>
                                <?php echo htmlspecialchars($cert); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>