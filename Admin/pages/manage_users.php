<?php include '../includes/header.php'; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h2>User Management</h2>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-2"></i> Create New Account
        </button>
    </div>

    <div class="card mt-3 shadow-sm">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
                    foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?= $user['full_name'] ?></strong></td>
                            <td><?= $user['email'] ?></td>
                            <td><span class="badge bg-secondary"><?= strtoupper($user['role']) ?></span></td>
                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info text-white me-2"
                                    onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-danger"
                                    onclick="confirmDelete(<?= $user['user_id'] ?>, '<?= addslashes($user['full_name']) ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="../actions/add_user.php" method="POST" id="addUserForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Create Account</h5>
                </div>
                <div class="modal-body">
                    <input type="text" name="full_name" class="form-control mb-3" placeholder="Full Name" required>
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                    <select name="role" class="form-select mb-3">
                        <option value="user">Standard User</option>
                        <option value="provider">Service Provider</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="../actions/edit_user.php" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Account</h5>
                    <button type="button" class="btn-close" data-bs-toggle="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <label class="small fw-bold">Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-control mb-3" required>
                    
                    <label class="small fw-bold">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control mb-3" required>
                    
                    <label class="small fw-bold">Role</label>
                    <select name="role" id="edit_role" class="form-select mb-3">
                        <option value="user">Standard User</option>
                        <option value="provider">Service Provider</option>
                        <option value="admin">Administrator</option>
                    </select>
                    
                    <div class="alert alert-info py-2 small">
                        Leave password blank to keep current password.
                    </div>
                    <label class="small fw-bold">New Password (Optional)</label>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Enter new password">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="../assets/js/manage_users.js"></script>
<?php include '../includes/footer.php'; ?>
<?php if (isset($_GET['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'The user has been successfully removed.',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= htmlspecialchars($_GET['error'] == "self_delete" ? "You cannot delete your own admin account!" : "Something went wrong.") ?>'
        });
    </script>
<?php endif; ?>