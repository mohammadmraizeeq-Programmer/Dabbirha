<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h2 class="mt-4"><i class="fas fa-star-half-alt me-2 text-warning"></i>Review Monitor</h2>
    <p class="text-muted">Monitor and moderate feedback left for service providers.</p>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Client</th>
                            <th>Provider</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetching reviews with user and provider names
                        // Note: review_text is used here to match your SQL schema
                        $query = "SELECT r.*, u.full_name as client_name, p_u.full_name as provider_name 
                                  FROM reviews r 
                                  JOIN users u ON r.user_id = u.user_id 
                                  JOIN providers p ON r.provider_id = p.provider_id 
                                  JOIN users p_u ON p.user_id = p_u.user_id 
                                  ORDER BY r.review_id DESC";
                        $stmt = $pdo->query($query);
                        
                        while($row = $stmt->fetch()): ?>
                        <tr data-aos="fade-up">
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($row['client_name']) ?></td>
                            <td><?= htmlspecialchars($row['provider_name']) ?></td>
                            <td>
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $row['rating'] ? 'text-warning' : 'text-light' ?>"></i>
                                <?php endfor; ?>
                            </td>
                            <td style="max-width: 300px;" class="text-truncate">
                                <?= htmlspecialchars($row['review_text']) ?>
                            </td>
                            <td class="small"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button onclick="deleteReview(<?= $row['review_id'] ?>)" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function deleteReview(id) {
    Swal.fire({
        title: 'Delete this review?',
        text: "This action cannot be undone and will affect the provider's rating.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // This matches your roadmap: Admin/actions/delete_review.php
            window.location.href = `../actions/delete_review.php?id=${id}`;
        }
    })
}
</script>

<?php include '../includes/footer.php'; ?>