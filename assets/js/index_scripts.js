

document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        easing: 'ease-in-out',
    });

    // Testimonial Slider Functionality
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.slider-arrow.prev');
    const nextBtn = document.querySelector('.slider-arrow.next');
    let currentSlide = 0;

    // Show slide function
    function showSlide(index) {
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Add active class to current slide and dot
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlide = index;
    }

    // Next slide function
    function nextSlide() {
        let nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    // Previous slide function
    function prevSlide() {
        let prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }

    // Event listeners for arrows
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);

    // Event listeners for dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
        });
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
        }
    });

    // Swipe support for touch devices
    let touchStartX = 0;
    let touchEndX = 0;
    
    const slider = document.querySelector('.testimonial-slider');
    
    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // Swipe left - next slide
                nextSlide();
            } else {
                // Swipe right - previous slide
                prevSlide();
            }
        }
    }

    // Button click handlers (if needed for other buttons)
    function handleUploadClick() {
        console.log("Upload Problem button clicked!");
        alert("Upload Problem feature would be activated here!");
    }

    function handleBrowseClick() {
        console.log("Browse Providers button clicked!");
        alert("Browse Providers feature would be activated here!");
    }
});
