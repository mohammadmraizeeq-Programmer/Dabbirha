// Function to redirect to job details
function viewJob(jobId) {
    if(jobId) {
        window.location.href = 'job_details.php?id=' + jobId;
    } else {
        console.error("Job ID is missing");
    }
}

// Global initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations if the library is loaded
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true
        });
    }

    console.log("Admin JS Loaded and Ready");
});
// Example fix for main.js
if (document.querySelector('.card')) {
    gsap.from(".card", { 
        duration: 1, 
        y: 30, 
        opacity: 0, 
        stagger: 0.2 
    });
}