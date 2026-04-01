<?php 
include '../includes/header.php'; 

/** * STATISTICAL CALCULATIONS 
 * Pulling totals from the transactions table populated by complete_job.php
 */

// 1. Total Gross Volume (All money moved through the platform)
$total_revenue = $pdo->query("SELECT SUM(total_quote) FROM transactions")->fetchColumn() ?: 0;

// 2. Total Net Admin Earnings (Your 10% share + debts collected)
$total_admin_earnings = $pdo->query("SELECT SUM(admin_commission) FROM transactions")->fetchColumn() ?: 0;

// 3. This Month's Performance
$current_month = date('Y-m');
$stmt_monthly = $pdo->prepare("SELECT SUM(admin_commission) FROM transactions WHERE created_at LIKE ?");
$stmt_monthly->execute([$current_month . '%']);
$monthly_earnings = $stmt_monthly->fetchColumn() ?: 0;

// 4. Job Statistics
$total_jobs = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn() ?: 0;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0 text-dark">Financial Intelligence Report</h2>
            <p class="text-muted">Tracking Dabbirha cash flow, debt collection, and PayPal settlements.</p>
        </div>
        <button class="btn btn-primary shadow-sm no-print" onclick="window.print()">
            <i class="fas fa-file-invoice me-2"></i>Print Official Report
        </button>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <small class="text-muted fw-bold text-uppercase">Gross Job Volume</small>
                <h3 class="fw-bold mb-0 mt-1"><?= number_format($total_revenue, 2) ?> <small class="fs-6">JOD</small></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <small class="text-muted fw-bold text-uppercase">Total Dabbirha Profit</small>
                <h3 class="fw-bold mb-0 mt-1 text-success"><?= number_format($total_admin_earnings, 2) ?> <small class="fs-6">JOD</small></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4">
                <small class="text-muted fw-bold text-uppercase">Earnings (<?= date('M') ?>)</small>
                <h3 class="fw-bold mb-0 mt-1 text-info"><?= number_format($monthly_earnings, 2) ?> <small class="fs-6">JOD</small></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <small class="text-muted fw-bold text-uppercase">Jobs Completed</small>
                <h3 class="fw-bold mb-0 mt-1"><?= $total_jobs ?></h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-exchange-alt me-2 text-primary"></i>Audit Trail & Money Flow</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Transaction Details</th>
                        <th>Method</th>
                        <th>Job Quote</th>
                        <th>PayPal Fee</th>
                        <th>Dabbirha (10%)</th>
                        <th>Debt Settled</th>
                        <th>Provider Payout</th>
                        <th class="pe-4">Net Dabbirha Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt = $pdo->query("
                        SELECT t.*, j.title, u.full_name as provider_name 
                        FROM transactions t 
                        JOIN jobs j ON t.job_id = j.job_id 
                        JOIN providers p ON t.provider_id = p.provider_id
                        JOIN users u ON p.user_id = u.user_id
                        ORDER BY t.created_at DESC
                    ");
                    while($row = $stmt->fetch()): 
                        $is_paypal = (strtolower($row['payment_method']) !== 'cash');
                        
                        // Calculate PayPal Fee (Standard 3.4% + 0.30 JOD)
                        $paypal_fee = $is_paypal ? ($row['total_quote'] * 0.034) + 0.30 : 0.00;
                        
                        // Net Profit for Admin on this job
                        $net_admin = $row['admin_commission'] + $row['debt_collected'] - $paypal_fee;
                    ?>
                        <tr>
                            <td class="ps-4">
                                <a href="job_details.php?id=<?= $row['job_id'] ?>" class="text-decoration-none">
                                    <div class="fw-bold text-primary">#<?= $row['job_id'] ?> <i class="fas fa-external-link-alt ms-1 smaller"></i></div>
                                </a>
                                <div class="small text-muted text-truncate" style="max-width: 150px;"><?= htmlspecialchars($row['title']) ?></div>
                                <div class="smaller text-secondary">By: <?= htmlspecialchars($row['provider_name']) ?></div>
                            </td>
                            <td>
                                <?php if($is_paypal): ?>
                                    <span class="badge bg-primary-light text-primary">ONLINE</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-light text-dark">CASH</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="fw-medium"><?= number_format($row['total_quote'], 2) ?></span></td>
                            
                            <td class="text-muted italic">
                                <?= $is_paypal ? '-' . number_format($paypal_fee, 2) : '0.00' ?>
                            </td>

                            <td class="text-success fw-bold">+ <?= number_format($row['admin_commission'], 2) ?></td>

                            <td class="<?= $row['debt_collected'] > 0 ? 'text-primary fw-bold' : 'text-muted' ?>">
                                <?= $row['debt_collected'] > 0 ? '+ ' . number_format($row['debt_collected'], 2) : '0.00' ?>
                            </td>

                            <td>
                                <div class="badge bg-light text-dark border p-2">
                                    <?= number_format($row['provider_payout'], 2) ?> JOD
                                </div>
                            </td>

                            <td class="pe-4 fw-bold text-dark bg-light-cell text-end">
                                <?= number_format($net_admin, 2) ?> JOD
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>