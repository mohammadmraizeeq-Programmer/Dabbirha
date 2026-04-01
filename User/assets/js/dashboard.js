document.addEventListener("DOMContentLoaded", () => {
    // Initialize AOS
    AOS.init({
        duration: 800,
        once: true
    });

    // Register GSAP Plugin
    gsap.registerPlugin(ScrollTrigger);

    // 3D Flip Animation
    const flipTL = gsap.timeline({
        scrollTrigger: {
            trigger: ".console-stage",
            start: "top+=150 top",
            toggleActions: "play none none reverse",
            once: false
        }
    });

    flipTL.to("#mainFlipper", {
        rotateX: 180,
        duration: 0.9,
        ease: "power3.inOut"
    });

    gsap.to(".bg-black i", {
        y: -40,
        scrollTrigger: {
            trigger: ".console-stage",
            start: "top+=150 top",
            toggleActions: "play reverse play reverse"
        }
    });

    // Radar Transition
    const launchRadar = document.getElementById("launchRadar");
    if (launchRadar) {
        launchRadar.addEventListener("click", () => {
            gsap.to("#mainFlipper", {
                scale: 6,
                rotateX: 200,
                opacity: 0,
                duration: 0.8,
                onComplete: () => location.href = "browse_providers.php"
            });
        });
    }
});

// --- ACCEPT / DECLINE LOGIC ---
// Kept outside DOMContentLoaded so it remains globally accessible to inline 'onclick' attributes
async function handleOffer(applicationId, action, jobId = null) {
    if (action === 'accept') {
        window.location.href = `job_details.php?id=${jobId}`;
        return;
    }

    const result = await Swal.fire({
        title: 'Decline this offer?',
        text: 'The provider will be notified and this bid will be removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, decline',
        confirmButtonColor: '#d33'
    });

    if (!result.isConfirmed) return;

    const fd = new FormData();
    fd.append('application_id', applicationId);
    fd.append('action', 'decline');

    try {
        const res = await fetch('../actions/process_offer.php', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Declined', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Something went wrong', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Network error or system failure', 'error');
    }
}