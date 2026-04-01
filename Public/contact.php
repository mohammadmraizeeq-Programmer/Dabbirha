<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../includes/config.php";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us | Dabbirha</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="../assets/css/contact_style.css">
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

            <a href="faqs.php" class="pill-item text-decoration-none">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
                        <path fill="#fff" d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
            </a>

        </div>
    </div>
    
    <div class="bg-service-icon d-none d-lg-block">
        <div class="floating-container"> <i class="ri-customer-service-fill main-icon"></i> <i class="ri-tools-line orbiting-icon"></i> <i class="ri-home-wifi-line orbiting-icon"></i> <i class="ri-drop-line orbiting-icon"></i> <i class="ri-flashlight-line orbiting-icon"></i>
            <div class="floating-dot"></div>
            <div class="floating-dot"></div>
            <div class="floating-dot"></div>
        </div>
    </div>

    <main class="modern-contact position-relative">
        <div class="container content-container">
            <div class="row justify-content-center align-items-center">

                <div class="col-lg-8 text-center" data-aos="fade-up">
                    <h1 class="display-4 fw-extrabold mb-4 main-heading">
                        Get in Touch <span class='together-word'>With Us</span>
                    </h1>
                    
                    <p class="lead mb-5 text-muted">
                        Contact us directly for all your home service needs. 
                        We're here to help you 7 days a week.
                    </p>

                    <div class="row justify-content-center g-4 mb-5">
                        <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
                            <div class="contact-card p-5 rounded-4 text-center h-100">
                                <div class="contact-icon-large mb-4">
                                    <i class="ri-phone-fill"></i>
                                </div>
                                <h3 class="mb-3">Call Us</h3>
                                <p class="text-muted mb-4">
                                    Speak directly with our customer service team
                                </p>
                                <a href="tel:+962796560382" class="btn btn-primary btn-lg w-100 phone-call-button">
                                    <i class="ri-phone-line me-2"></i>
                                    <span class="phone-number" dir="ltr">+962 7 9656 0382</span>
                                </a>
                                <p class="mt-3 mb-0 text-muted small">
                                    <i class="ri-time-line me-1"></i>
                                    Available 24/7 for emergencies
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
                            <div class="contact-card p-5 rounded-4 text-center h-100">
                                <div class="contact-icon-large mb-4">
                                    <i class="ri-mail-fill"></i>
                                </div>
                                <h3 class="mb-3">Email Us</h3>
                                <p class="text-muted mb-4">
                                    Send an email to receive detailed information
                                </p>
                                <a href="mailto:dabbirha@gmail.com" class="btn btn-primary btn-lg w-100 email-button">
                                    <i class="ri-mail-line me-2"></i>
                                    <span class="email-address">dabbirha@gmail.com</span>
                                </a>
                                <p class="mt-3 mb-0 text-muted small">
                                    <i class="ri-time-line me-1"></i>
                                    Response within 2 hours
                                </p>
                            </div>
                        </div>
                    </div>

               

               
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="../assets/js/contact_scripts.js"></script>
</body>
</html>