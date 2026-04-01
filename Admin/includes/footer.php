</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="/DABBIRHA/Admin/assets/js/main.js"></script>
<script>
    AOS.init();

    // Creative GSAP Entrance
const cards = document.querySelectorAll(".card");
if (cards.length > 0) {
    gsap.from(".card", {
        duration: 0.8,
        y: 30,
        opacity: 0,
        stagger: 0.2
    });
}
    // Delete User SweetAlert
    function deleteUser(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the account!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../actions/delete_user.php?id=${id}`;
            }
        })
    }
</script>
</body>

</html>