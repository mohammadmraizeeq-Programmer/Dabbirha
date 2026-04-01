<?php
// Start session to access logged-in user data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- DYNAMIC PATH CALCULATION ---
// This ensures all links work from Admin/pages/ OR Chat/pages/
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/DABBIRHA/";

// Use absolute path for the config file to avoid folder depth issues
require_once $_SERVER['DOCUMENT_ROOT'] . '/DABBIRHA/includes/config.php';

if (!isset($pdo)) {
    die("Database connection missing.");
}

// Redirect if not logged in or not an admin (Security layer)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_url . "Reg/signin/pages/signin.php");
    exit();
}

// Fetch current admin details from DB to ensure data is fresh
$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ? AND role = 'admin'");
$stmt->execute([$admin_id]);
$admin_user = $stmt->fetch();

$display_name = $admin_user ? $admin_user['full_name'] : "System Admin";

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dabberha Admin | Control Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="<?php echo $base_url; ?>Admin/assets/css/admin-style.css">
</head>

<body>

    <div class="admin-layout">
        <aside class="sidebar no-print">
            <a href="<?php echo $base_url; ?>Admin/pages/dashboard.php" class="sidebar-brand">
                <div class="brand-logo">D</div>
                <span class="brand-name">
                    Dabbirha<span style="color:var(--side-active)">.</span>
                </span>
            </a>

            <nav class="sidebar-nav">
                <small class="nav-section">Main Dashboard</small>
                <a href="<?php echo $base_url; ?>Admin/pages/dashboard.php" class="nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> <span>Overview</span>
                </a>

                <small class="nav-section">Management</small>
                <a href="<?php echo $base_url; ?>Admin/pages/manage_users.php" class="nav-item <?= $current_page == 'manage_users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> <span>Users</span>
                </a>
                <a href="<?php echo $base_url; ?>Admin/pages/manage_providers.php" class="nav-item <?= $current_page == 'manage_providers.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-tie"></i> <span>Providers</span>
                </a>
                <a href="<?php echo $base_url; ?>Admin/pages/manage_jobs.php" class="nav-item <?= ($current_page == 'manage_jobs.php' || $current_page == 'job_details.php') ? 'active' : '' ?>">
                    <i class="fas fa-briefcase"></i> <span>Job Requests</span>
                </a>

                <small class="nav-section">Quality Control</small>
                <a href="<?php echo $base_url; ?>Admin/pages/manage_reviews.php" class="nav-item <?= $current_page == 'manage_reviews.php' ? 'active' : '' ?>">
                    <i class="fas fa-star"></i> <span>Reviews</span>
                </a>

                <small class="nav-section">Financials</small>
                <a href="<?php echo $base_url; ?>Admin/pages/transactions.php" class="nav-item <?= $current_page == 'transactions.php' ? 'active' : '' ?>">
                    <i class="fas fa-wallet"></i> <span>Transactions</span>
                </a>

                <a href="<?php echo $base_url; ?>Admin/pages/reports.php" class="nav-item <?= $current_page == 'reports.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> <span>Reports</span>
                </a>
                <a href="<?php echo $base_url; ?>Admin/pages/chatbot_history.php" class="nav-item <?= $current_page == 'chatbot_history.php' ? 'active' : '' ?>">
                    <i class="fas fa-robot"></i> <span>AI Assistant Logs</span>
                </a>
                <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <a href="<?php echo $base_url; ?>index.php" class="nav-item logout">
                        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <?php if ($current_page === 'transactions.php'): ?>
            <link rel="stylesheet" href="<?php echo $base_url; ?>Admin/assets/css/transactions.css">
        <?php endif; ?>
        <?php if ($current_page === 'reports.php'): ?>
            <link rel="stylesheet" href="<?php echo $base_url; ?>Admin/assets/css/financial-report.css">
        <?php endif; ?>

        <main class="main-body">