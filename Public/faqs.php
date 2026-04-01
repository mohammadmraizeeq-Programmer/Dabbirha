<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs | Dabbirha Service Marketplace</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/faqs_style.css">
</head>

<body>

    <div class="pill-navbar-wrapper">
        <div class="pill-navbar">

            <a href="../index.php" class="pill-item active text-decoration-none">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
                        <path fill="#fff" d="M12 3l9 8h-3v10h-5v-6H11v6H6V11H3z" />
                    </svg>
                </div>
                <span>Home</span>
            </a>

            <a href="../index.php#services" class="pill-item text-decoration-none">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
                        <path fill="#fff" d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z" />
                    </svg>
                </div>
            </a>

            <a href="policy.php" class="pill-item text-decoration-none">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
                        <path fill="#fff" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z" />
                    </svg>
                </div>
            </a>

        </div>
    </div>

    <main>

        <section class="faq-hero">
            <div class="container text-center">
                <h1 class="display-3 fw-bold mb-4">
                    How can we help?
                </h1>

                <p class="lead opacity-75 mb-5">
                    Find answers regarding AI Analysis, Payments, and Marketplace Rules.
                </p>

                <div class="search-container">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 ps-4 rounded-pill-start">
                            <i class="ri-search-2-line text-muted"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control search-input border-0 rounded-pill-end"
                            placeholder="Search for keywords (e.g. 'AI', 'PayPal', 'Jobs')...">
                    </div>
                </div>
            </div>
        </section>

        <section class="faq-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-9">

                        <div class="accordion" id="dabbirhaFaq">

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        <i class="ri-cpu-line fs-4 mx-2"></i>
                                        How does the AI diagnostic tool work?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        When you upload a photo or video of your problem, our system uses Google AI (Vision & Video) to analyze the media. It automatically suggests the correct service category and labels, helping you find the right expert faster.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        <i class="ri-map-pin-range-line fs-4 mx-2"></i>
                                        How do I choose between browsing providers and uploading a job?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        You can browse providers on an interactive map or upload your problem and receive offers from providers. Choose the option that best fits your needs and budget.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        <i class="ri-paypal-line fs-4 mx-2"></i>
                                        What payment methods are supported?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        Payments are supported via PayPal and Cash on Delivery. Online payments are held securely and released only after the job is completed.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        <i class="ri-percent-line fs-4 mx-2"></i>
                                        What is the 10% commission fee?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        Dabbirha charges a 10% commission on completed jobs to maintain the platform and AI services. This fee is handled automatically.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq5">
                                        <i class="ri-shield-keyhole-line fs-4 mx-2"></i>
                                        Is my data and media safe?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        Yes. We use secure database practices and safe file handling. Deleted jobs permanently remove all associated media from our servers.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq6">
                                        <i class="ri-briefcase-line fs-4 mx-2"></i>
                                        How do I become a provider?
                                    </button>
                                </h2>
                                <div id="faq6" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        Register as a provider, verify your email and identity, set your location, and start bidding on jobs through your dashboard.
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="text-center mt-5">
                            <div class="p-5 bg-white rounded-4 shadow-sm border">
                                <h3 class="fw-bold mb-3">Still need assistance?</h3>
                                <p class="text-muted mb-4">Our support team is available 24/7 for users and providers.</p>
                                <a href="contact.php" class="btn btn-premium">
                                    <i class="ri-customer-service-2-fill mx-2"></i>
                                    Contact Support Center
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="../assets/js/faqs_scripts.js"></script>

</body>

</html>