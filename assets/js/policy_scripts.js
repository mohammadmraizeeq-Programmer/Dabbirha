
    AOS.init({ duration: 1000, once: true });

    // Scroll Progress & Active Link Script
    window.onscroll = function() {
        var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var scrolled = (winScroll / height) * 100;
        document.getElementById("scrollProgress").style.width = scrolled + "%";

        // Manual Sidebar Active Toggle
   
    };

    document.addEventListener('DOMContentLoaded', function () {
    const scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: '#policy-nav',
        offset: 120
    });
});
