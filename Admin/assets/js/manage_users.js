function confirmDelete(userId, userName) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete " + userName + ". This will permanently remove their profile, jobs, and reviews!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to your existing delete_user.php script
            window.location.href = "../actions/delete_user.php?id=" + userId;
        }
    });
}
function editUser(user) {
    // Fill the hidden ID and input fields
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;

    // Show the modal
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}