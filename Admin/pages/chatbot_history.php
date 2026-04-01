<?php
// 1. Path to header
include "../includes/header.php"; 

// 2. Database Logic (The $pdo variable is inherited from header.php)
try {
    $query = "SELECT l.*, u.full_name, u.email 
              FROM chatbot_logs l 
              LEFT JOIN users u ON l.user_id = u.user_id 
              ORDER BY l.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = $e->getMessage();
}
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--text-dark);">AI Assistant Logs</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item active">Chatbot Logs</li>
                </ol>
            </nav>
        </div>
        <div class="stats-badge bg-white shadow-sm px-4 py-2 rounded-3 border">
            <span class="text-primary fw-bold" style="font-size: 1.2rem;"><?= count($logs ?? []) ?></span> 
            <small class="text-muted ms-1">Total Logs</small>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>SQL Error:</strong> <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="ps-4 py-3" style="font-weight: 600; color: #4b5563;">User / Source</th>
                        <th class="py-3" style="font-weight: 600; color: #4b5563;">User Query</th>
                        <th class="py-3" style="font-weight: 600; color: #4b5563;">AI Response</th>
                        <th class="py-3" style="font-weight: 600; color: #4b5563; width: 180px;">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-4">
                                <?php if ($log['user_id']): ?>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; border-radius: 50%; background: #e0f2fe;">
                                            <?= strtoupper(substr($log['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($log['full_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($log['email']) ?></small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center text-secondary">
                                        <div class="avatar-circle me-3 bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; border-radius: 50%; border: 1px dashed #cbd5e1;">
                                            <i class="fas fa-user-secret"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="font-size: 0.9rem;">Guest User</div>
                                            <small class="text-muted">Landing Page</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark" style="font-size: 0.85rem; max-width: 250px; line-height: 1.5;">
                                    "<?= htmlspecialchars($log['user_query']) ?>"
                                </div>
                            </td>
                            <td>
                                <div class="p-2 rounded-3 text-muted" style="font-size: 0.85rem; max-width: 400px; background: #fcfcfc; border: 1px solid #f1f5f9;">
                                    <?= htmlspecialchars($log['bot_response']) ?>
                                </div>
                            </td>
                            <td class="text-muted" style="font-size: 0.8rem;">
                                <div><i class="far fa-calendar me-1"></i> <?= date('d M, Y', strtotime($log['created_at'])) ?></div>
                                <div class="mt-1"><i class="far fa-clock me-1"></i> <?= date('h:i A', strtotime($log['created_at'])) ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-robot fa-3x text-light mb-3"></i>
                                    <p class="text-muted fw-medium">No chatbot interactions found in the database.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// 3. Footer closes the main-body and admin-layout
include "../includes/footer.php"; 
?>