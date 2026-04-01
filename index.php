<?php

include "includes/config.php";
$sql = "
SELECT 
    r.review_id,
    r.rating,
    r.review_text,
    r.created_at,
    u.full_name,
    u.user_id,
    p.provider_id,
    p.image as provider_image
FROM reviews r
JOIN users u ON r.user_id = u.user_id
JOIN providers p ON r.provider_id = p.provider_id
WHERE r.rating >= 4.0  -- Only show good reviews (4+ stars)
ORDER BY r.created_at DESC
LIMIT 3
";

$result = $conn->query($sql);
$reviews = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

// Close connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dabbirha | Local Service Solution</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/index_style.css">
</head>

<body>

    <?php include "includes/header.php"; ?>

    <section class="hero hero-bg pb-5">
        <div class="container">
            <div class="row align-items-center">

                <div class="col-lg-6 hero-content">
                    <div class="hero-text">
                        <h1 class="hero-title mb-3">
                            Your Local Service Solution
                        </h1>

                        <p class="hero-description mb-4">
                            Find the best technicians, from plumbers to electricians, right in your neighborhood. Book, manage, and review local professionals with ease.
                        </p>

                        <div class="d-flex gap-3 action-buttons">
                            <button type="button"
                                class="btn d-flex align-items-center justify-content-center fw-bold btn-start"
                                onclick="window.location.href='Reg/signin/pages/signin.php'">
                                Get Started
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-visual">
                        <div class="service-promo-card card-1 shadow-lg">
                            <i class="ri-shield-check-line" style="color: var(--card_one_icon_color)"></i>
                            <h3 class="mt-0">Verified Experts</h3>
                            <p>Only trusted, vetted professionals.</p>
                        </div>

                        <div class="service-promo-card card-2 shadow-lg">
                            <i class="ri-calendar-check-line" style="color: var(--card_two_icon_color)"></i>
                            <h3 class="mt-0">Easy Booking</h3>
                            <p>Schedule your service in minutes.</p>
                        </div>

                        <div class="service-promo-card card-3 shadow-lg">
                            <i class="ri-chat-3-line" style="color: var(--card_three_icon_color)"></i>
                            <h3 class="mt-0">Direct Chat</h3>
                            <p>Communicate with your pro easily.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="how-it-works" id="how-it-works">
        <div class="container py-5">
            <div class="section-header text-center mb-5">
                <h2 class="display-5">How It Works</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Your guide to getting the best local services in just a few steps.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="step">
                        <div class="step-icon">
                            <i class="ri-brain-line"></i>
                        </div>
                        <h3 class="fw-bold mt-4">AI Analysis & Provider Matching</h3>
                        <p class="text-muted">Upload your issue for AI analysis. Providers review your request and may accept or decline it. Once accepted, you can confirm and move forward with confidence.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="step">
                        <div class="step-icon">
                            <i class="ri-user-search-line"></i>
                        </div>
                        <h3 class="fw-bold mt-4">Compare & Select</h3>
                        <p class="text-muted">Review profiles, compare ratings, read reviews, and choose the perfect expert for your job.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="step">
                        <div class="step-icon">
                            <i class="ri-calendar-line"></i>
                        </div>
                        <h3 class="fw-bold mt-4">Contact & Schedule</h3>
                        <p class="text-muted">Get in touch with a provider and set a convenient date and time.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="step">
                        <div class="step-icon">
                            <i class="ri-hand-coin-line"></i>
                        </div>
                        <h3 class="fw-bold mt-4">Pay & Review</h3>
                        <p class="text-muted">The job is completed, make your payment and leave a review to help others.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="services-section" id="services">
        <div class="container py-5">

            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h2 class="display-5">Provider Services</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    We connect you with trusted professionals for all your home service needs.
                </p>
            </div>

            <div class="row g-4">

                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?auto=format&fit=crop&w=800&q=80"
                                alt="Professional plumber fixing pipes" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h3>Plumbing</h3>
                            <p>Fix leaks, install fixtures, and solve all your plumbing issues with certified professionals.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://www.sirtbhopal.ac.in/assets/images/blogs/electrical-engineering-is-the-life-line-for-civilization.jpg"
                                alt="Electrician working on electrical panel" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h3>Electrical</h3>
                            <p>From wiring repairs to installations, our electricians ensure your home is safe and powered.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80"
                                alt="Carpenter measuring wood" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h3>Carpentry</h3>
                            <p>Custom furniture, repairs, and installations by skilled carpenters who care about quality.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://img.freepik.com/premium-photo/man-paints-white-wall-with-roller_38145-1517.jpg?semt=ais_hybrid&w=740&q=80"
                                alt="Painter working on a wall" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h3>Painting</h3>
                            <p>Interior and exterior painting services to refresh and transform your living spaces.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://deax38zvkau9d.cloudfront.net/prod/assets/images/uploads/services/1694507881house-cleaning-ajman.webp?f=webp&w=768"
                                alt="Professional cleaner vacuuming" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h3>Home Cleaning</h3>
                            <p>Thorough cleaning services to keep your home sparkling and hygienic.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://we4u.ind.in/blog/wp-content/uploads/2023/02/split-ac-service.jpg"
                                alt="AC technician repairing air conditioner" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h3>AC Repair</h3>
                            <p>Keep cool with our expert AC maintenance, repair, and installation services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="testimonials-section" id="testimonials">
        <div class="container py-5">

            <!-- Section Header -->
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h2 class="display-5">What Our Customers Say</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Join thousands of satisfied customers who found their perfect solutions through Dabbirha
                </p>
            </div>

            <?php if (!empty($reviews)): ?>

                <!-- Slider -->
                <div class="row g-4 testimonial-slider position-relative">

                    <!-- Navigation Arrows (MUST be inside slider) -->
                    <div class="slider-navigation">
                        <button class="slider-arrow prev" aria-label="Previous">
                            <i class="ri-arrow-left-s-line"></i>
                        </button>
                        <button class="slider-arrow next" aria-label="Next">
                            <i class="ri-arrow-right-s-line"></i>
                        </button>
                    </div>

                    <!-- Slides -->
                    <?php foreach ($reviews as $index => $review): ?>
                        <div class="col-12 testimonial-slide <?= $index === 0 ? 'active' : '' ?>"
                            data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">

                            <div class="testimonial-card shadow-sm border-0 p-4 rounded-4 bg-white">

                                <!-- Rating -->
                                <div class="rating mb-3">
                                    <?php
                                    $rating = (float) $review['rating'];
                                    for ($i = 1; $i <= 5; $i++):
                                        if ($i <= floor($rating)): ?>
                                            <i class="ri-star-fill text-warning"></i>
                                        <?php elseif (($rating - floor($rating)) >= 0.5 && $i == floor($rating) + 1): ?>
                                            <i class="ri-star-half-fill text-warning"></i>
                                        <?php else: ?>
                                            <i class="ri-star-line text-warning"></i>
                                    <?php endif;
                                    endfor; ?>
                                    <span class="ms-2 fw-bold"><?= number_format($rating, 1) ?></span>
                                </div>

                                <!-- Review Text -->
                                <p class="mb-4 fs-5 fst-italic">
                                    "<?= htmlspecialchars($review['review_text'], ENT_QUOTES) ?>"
                                </p>

                                <!-- User -->
                                <div class="d-flex align-items-center">
                                    <?php
                                    $profilePic = !empty($review['provider_image'])
                                        ? htmlspecialchars($review['provider_image'])
                                        : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
                                    ?>
                                    <img src="<?= $profilePic ?>"
                                        alt="<?= htmlspecialchars($review['full_name']) ?>"
                                        class="rounded-circle me-3"
                                        style="width: 50px; height: 50px; object-fit: cover;">

                                    <div>
                                        <h5 class="mb-0 fw-bold">
                                            <?= htmlspecialchars($review['full_name']) ?>
                                        </h5>
                                        <small class="text-muted">
                                            <?= date('F j, Y', strtotime($review['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <!-- Dots -->
                <div class="slider-dots text-center mt-4">
                    <?php foreach ($reviews as $index => $review): ?>
                        <span class="dot <?= $index === 0 ? 'active' : '' ?>"
                            data-slide="<?= $index ?>"></span>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div>
    </section>

    <section class="about-section py-5" id="about">
        <div class="container py-lg-5">

            <div class="row align-items-center mb-5 pb-4">
                <div class="col-lg-5 content-column" data-aos="fade-right">
                    <div class="section-accent"></div>
                    <h2 class="display-4 fw-bold mb-4" style="color: #2e3a59;">
                        About Dabbirha
                    </h2>
                    <p class="lead text-muted">
                        The smart way to connect with service providers - with or without AI assistance.
                    </p>
                </div>

                <div class="col-lg-7" data-aos="fade-left">
                    <div class="about-description">
                        <p class="text-secondary mb-4" style="font-size: 1.15rem; line-height: 1.9;">
                            At Dabbirha, we've created <strong>dual pathways</strong> for service connections. Choose the <strong>traditional method</strong> and browse verified providers, or opt for our <strong>innovative AI solution</strong>. Simply take a photo of your home problem - a leaky pipe, electrical issue, or anything else - and our AI will analyze it to understand your exact needs.
                        </p>
                        <p class="text-secondary" style="font-size: 1.15rem; line-height: 1.9;">
                            Providers review AI-analyzed requests in their dashboard and submit offers. You then review these offers, communicate directly with providers, and negotiate terms. Both parties must mutually agree before any work begins, ensuring <strong>complete transparency and satisfaction</strong> for everyone involved.
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-item p-4 bg-white rounded-4">
                        <div class="feature-icon-wrapper mb-4">
                            <div class="icon-box" style="background: rgba(13, 110, 253, 0.08);">
                                <i class="ri-route-line" style="color: #0d6efd; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #2e3a59;">Your Choice, Your Way</h5>
                        <p class="text-muted small lh-lg mb-0">Browse providers traditionally or use AI photo analysis. You control how you connect with service professionals.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-item p-4 bg-white rounded-4">
                        <div class="feature-icon-wrapper mb-4">
                            <div class="icon-box" style="background: rgba(22, 86, 143, 0.08);">
                                <i class="ri-camera-line" style="color: #16568f; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #2e3a59;">AI-Powered Analysis</h5>
                        <p class="text-muted small lh-lg mb-0">Upload photos of problems. Our AI analyzes and creates detailed service requests for providers.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-item p-4 bg-white rounded-4">
                        <div class="feature-icon-wrapper mb-4">
                            <div class="icon-box" style="background: rgba(125, 43, 176, 0.08);">
                                <i class="ri-check-double-line" style="color: #7d2bb0; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #2e3a59;">Mutual Agreement System</h5>
                        <p class="text-muted small lh-lg mb-0">Providers submit offers, users review them. Both must accept before work begins. No pressure, just clear agreements.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-item p-4 bg-white rounded-4">
                        <div class="feature-icon-wrapper mb-4">
                            <div class="icon-box" style="background: rgba(14, 123, 107, 0.08);">
                                <i class="ri-message-3-line" style="color: #0e7b6b; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #2e3a59;">Secure Communication</h5>
                        <p class="text-muted small lh-lg mb-0">Built-in chat for discussing details, pricing, and timelines. Everything stays within our secure platform.</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 pt-4" data-aos="fade-up" data-aos-delay="500">
                <a href="Public/contact.php" class="btn btn-premium shadow-lg">
                    <i class="ri-discuss-line me-2"></i> Connect with Our Team
                </a>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/index_scripts.js"></script>
<?php include_once 'Chatbot/chatbot_widget.php'; ?>
</body>

</html>
