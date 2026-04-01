<?php
include '../includes/header.php';

// 1. Get the filter value from the URL (if set)
$method_filter = isset($_GET['method']) ? $_GET['method'] : 'All';

// 2. Build the dynamic SQL query with Joins to get names and job titles
$queryStr = "SELECT t.*, 
                    u_client.full_name as client_name, 
                    u_prov.full_name as provider_name,
                    j.title as job_title
             FROM transactions t
             JOIN jobs j ON t.job_id = j.job_id
             JOIN users u_client ON j.user_id = u_client.user_id
             JOIN providers p ON t.provider_id = p.provider_id
             JOIN users u_prov ON p.user_id = u_prov.user_id";

if ($method_filter !== 'All') {
    $queryStr .= " WHERE t.payment_method = :method";
}

$queryStr .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($queryStr);
if ($method_filter !== 'All') {
    $stmt->execute(['method' => strtolower($method_filter)]);
} else {
    $stmt->execute();
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Transaction Ledger</h2>
            <p class="text-muted">A complete history of all financial movements on the platform.</p>
        </div>

        <div class="dropdown no-print">
            <button class="btn btn-white border shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-filter me-2 text-primary"></i> Method: <?= $method_filter ?>
            </button>
            <ul class="dropdown-menu shadow border-0">
                <li><a class="dropdown-item" href="transactions.php?method=All">All Methods</a></li>
                <li><a class="dropdown-item" href="transactions.php?method=Paypal">PayPal / Online</a></li>
                <li><a class="dropdown-item" href="transactions.php?method=Cash">Cash</a></li>
            </ul>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Job Info</th>
                        <th>Participants</th>
                        <th>Total Quote</th>
                        <th>Admin Fee</th>
                        <th>Debt Settled</th>
                        <th>Net Payout</th>
                        <th>Method</th>
                        <th class="pe-4">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch()): ?>
                        <tr>
                            <td class="ps-4">
                                <a href="job_details.php?id=<?= $row['job_id'] ?>" class="text-decoration-none fw-bold">
                                    #<?= $row['job_id'] ?>
                                </a>
                                <div class="small text-muted text-truncate" style="max-width: 150px;">
                                    <?= htmlspecialchars($row['job_title']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="small"><strong>C:</strong> <?= htmlspecialchars($row['client_name']) ?></div>
                                <div class="small text-primary"><strong>P:</strong> <?= htmlspecialchars($row['provider_name']) ?></div>
                            </td>
                            <td class="fw-bold text-dark"><?= number_format($row['total_quote'], 2) ?></td>
                            <td class="text-success small">+<?= number_format($row['admin_commission'], 2) ?></td>
                            <td class="text-info small"><?= $row['debt_collected'] > 0 ? '+' . number_format($row['debt_collected'], 2) : '0.00' ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= number_format($row['provider_payout'], 2) ?> JOD
                                </span>
                            </td>
                            <td>
                                <?php if (in_array(strtolower($row['payment_method']), ['paypal', 'visa'])): ?>
                                    <span class="badge bg-primary-light text-primary">ONLINE</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-light text-dark">CASH</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 small text-muted">
                                <?= date('d M, Y', strtotime($row['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>