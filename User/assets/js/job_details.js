document.addEventListener('DOMContentLoaded', () => {
    // 1. GSAP Page Animations
    gsap.from(".glass-card", {
        y: 50,
        opacity: 0,
        duration: 1,
        ease: "power4.out",
        stagger: 0.2
    });

    gsap.from(".mission-header h1", {
        x: -30,
        opacity: 0,
        duration: 0.8,
        delay: 0.2
    });

    // 2. Original Payment Logic
    const cashBtn = document.getElementById('confirm-cash-btn');
    const paypalContainer = document.getElementById('paypal-button-container');
    const jobDataEl = document.getElementById('job-data');
    const cashRadio = document.getElementById('pay_cash');
    const paypalRadio = document.getElementById('pay_paypal');

    function togglePayment() {
        if (cashRadio && cashRadio.checked) {
            if (cashBtn) cashBtn.style.display = 'block';
            if (paypalContainer) paypalContainer.style.display = 'none';
        } else if (paypalRadio && paypalRadio.checked) {
            if (cashBtn) cashBtn.style.display = 'none';
            if (paypalContainer) paypalContainer.style.display = 'block';
        }
    }

    if (cashRadio && paypalRadio) {
        cashRadio.addEventListener('change', togglePayment);
        paypalRadio.addEventListener('change', togglePayment);
        togglePayment();
    }

    // 3. Original Data & Actions
    if (!jobDataEl) return;
    const jobId = jobDataEl.getAttribute('data-job-id');
    const appId = jobDataEl.getAttribute('data-app-id');
    const paypalAmount = jobDataEl.getAttribute('data-paypal-amount');

    if (cashBtn) {
        cashBtn.addEventListener('click', async () => {
            const result = await Swal.fire({
                title: 'Confirm Hiring',
                text: "You are choosing to pay with Cash upon completion. Proceed?",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#008770',
                confirmButtonText: 'Yes, Hire Now'
            });
            if (result.isConfirmed) {
                window.location.href = `../actions/process_hire.php?method=cash&job_id=${jobId}&app_id=${appId}`;
            }
        });
    }

    if (window.paypal && paypalContainer) {
        paypal.Buttons({
            createOrder: (data, actions) => {
                return actions.order.create({
                    purchase_units: [{
                        description: `Service Payment for Job #${jobId}`,
                        amount: { currency_code: 'USD', value: paypalAmount.toString() }
                    }]
                });
            },
            onApprove: (data, actions) => {
                return actions.order.capture().then(() => {
                    window.location.href = `../actions/process_hire.php?method=paypal&job_id=${jobId}`;
                });
            }
        }).render('#paypal-button-container');
    }
});

// Original completeJob function
async function completeJob(jobId) {
    const { value: rating } = await Swal.fire({
        title: 'Complete Mission?',
        text: 'Rate your experience to release payment.',
        icon: 'success',
        input: 'select',
        inputOptions: { '5': '⭐⭐⭐⭐⭐', '4': '⭐⭐⭐⭐', '3': '⭐⭐⭐', '2': '⭐⭐', '1': '⭐' },
        inputPlaceholder: 'Select a rating',
        showCancelButton: true,
        confirmButtonColor: '#008770'
    });

    if (!rating) return;
    const formData = new FormData();
    formData.append('job_id', jobId);
    formData.append('rating', rating);

    try {
        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        const res = await fetch('../actions/complete_job.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Success!', 'Payment released.', 'success').then(() => location.reload());
        }
    } catch (e) { console.error(e); }
}