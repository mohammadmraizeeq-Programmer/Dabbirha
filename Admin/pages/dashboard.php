<?php include '../includes/header.php'; 

/** * DASHBOARD LOGIC - Matching your DB Schema
 */

// 1. Total Clients (Users table)
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// 2. Total active Providers (Providers table)
$active_providers = $pdo->query("
    SELECT COUNT(*) 
    FROM providers p
    JOIN users u ON p.user_id = u.user_id
    WHERE u.verified = 1
")->fetchColumn();

// 3. Open Jobs (Jobs table - status 'open')
$pending_jobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn();

// 4. Revenue (Commission from jobs marked is_paid)
$total_revenue = $pdo->query("SELECT SUM(commission_amount) FROM jobs WHERE is_paid = 1")->fetchColumn() ?? 0;
?>

<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-up">
        <div>
            <h2 class="fw-bold mb-1">Admin Dashboard</h2>
            <p class="text-muted">Overview of Dabberha platform activity.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="reports.php" class="btn btn-white shadow-sm rounded-pill px-4 py-2 border no-print">
                <i class="fas fa-chart-line me-2"></i> View Reports
            </a>
            <button class="btn btn-primary shadow-sm rounded-pill px-4 py-2" onclick="window.print()">
                <i class="fas fa-file-pdf me-2"></i> Export PDF
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5" data-aos="fade-up" data-aos-delay="100">
        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-4 h-100 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-success"><i class="fas fa-wallet"></i></div>
                    <span class="trend-badge positive">Revenue</span>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Earnings</span>
                <h3 class="fw-bold mt-1 mb-0"><?= number_format($total_revenue, 2) ?> JOD</h3>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-4 h-100 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-primary"><i class="fas fa-users"></i></div>
                    <span class="trend-badge positive">Clients</span>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Total Users</span>
                <h3 class="fw-bold mt-1 mb-0"><?= $total_users ?></h3>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-4 h-100 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-info"><i class="fas fa-user-tie"></i></div>
                    <span class="trend-badge neutral">Active</span>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Providers</span>
                <h3 class="fw-bold mt-1 mb-0"><?= $active_providers ?></h3>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-4 h-100 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-warning"><i class="fas fa-tasks"></i></div>
                    <span class="trend-badge warning">New</span>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Open Jobs</span>
                <h3 class="fw-bold mt-1 mb-0"><?= $pending_jobs ?></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
            <div class="glass-card border-0 shadow-sm">
                <div class="p-4 d-flex justify-content-between align-items-center border-bottom border-light">
                    <h5 class="fw-bold mb-0">Recent Job Activity</h5>
                    <a href="manage_jobs.php" class="btn btn-link btn-sm text-primary text-decoration-none fw-bold">Manage All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle custom-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Client Name</th>
                                <th>Service Type</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Joining jobs with users to get the name
                            $recent_jobs = $pdo->query("SELECT j.*, u.full_name 
                                                      FROM jobs j 
                                                      JOIN users u ON j.user_id = u.user_id 
                                                      ORDER BY j.created_at DESC LIMIT 6");
                            
                            while($job = $recent_jobs->fetch()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-soft-primary text-primary me-3 d-flex align-items-center justify-content-center fw-bold">
                                            <?= strtoupper(substr($job['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold small"><?= htmlspecialchars($job['full_name']) ?></span>
                                            <small class="text-muted" style="font-size: 0.65rem;"><?= date('M d, Y', strtotime($job['created_at'])) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-pill bg-soft-info text-dark" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars(str_replace('_', ' ', $job['service_type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-<?= ($job['urgency_level'] == 'high') ? 'danger' : 'muted' ?> small fw-bold">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i> <?= ucfirst($job['urgency_level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $status_color = match($job['status']) {
                                        'open' => 'warning',
                                        'in_progress' => 'info',
                                        'closed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="status-pill <?= $status_color ?>"><?= ucfirst($job['status']) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="job_details.php?id=<?= $job['job_id'] ?>" class="btn-icon shadow-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="glass-card p-4 shadow-sm h-100">
                <h5 class="fw-bold mb-4">Quick Navigation</h5>
                <div class="d-grid gap-3">
                    <a href="manage_users.php" class="btn btn-light border py-3 rounded-4 text-start px-4">
                        <i class="fas fa-users-cog text-primary me-3"></i> User Directory
                    </a>
                    <a href="manage_providers.php" class="btn btn-light border py-3 rounded-4 text-start px-4">
                        <i class="fas fa-user-check text-info me-3"></i> Provider Approval
                    </a>
                    <a href="transactions.php" class="btn btn-light border py-3 rounded-4 text-start px-4">
                        <i class="fas fa-file-invoice-dollar text-success me-3"></i> Revenue Log
                    </a>
                    <a href="manage_reviews.php" class="btn btn-light border py-3 rounded-4 text-start px-4">
                        <i class="fas fa-star text-warning me-3"></i> Review Monitor
                    </a>
                    
                    <div class="p-4 bg-soft-primary rounded-4 mt-3 border border-primary border-opacity-10">
                        <h6 class="fw-bold text-primary mb-2"><i class="fas fa-info-circle me-2"></i> Admin Tip</h6>
                        <p class="small text-muted mb-0">Use the <b>Job Details</b> page to manually assign providers to open requests if they remain unassigned for over 24 hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>