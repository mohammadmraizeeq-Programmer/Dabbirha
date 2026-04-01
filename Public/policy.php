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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal & Transparency | Home Service Jordan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/policy_style.css">
</head>

<body data-bs-spy="scroll" data-bs-target="#policy-nav" data-bs-offset="100">

    <div class="pill-navbar-wrapper">
        <div class="pill-navbar">

            <a href="index.php" class="pill-item active text-decoration-none">
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

            <a href="contact.php" class="pill-item text-decoration-none">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
                        <path fill="#fff" d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v3c0 .6.4 1 1 1h.5c.2 0 .4-.1.6-.2l2.7-2.7H20c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" />
                    </svg>
                </div>
            </a>

        </div>
    </div>

    <div id="scrollProgress"></div>

    <section class="hero-section text-center">
        <div class="container" data-aos="zoom-out">
            <h1 class="display-3 fw-bold">Platform Policies</h1>
            <p class="lead opacity-75">Clear rules for a safer home service marketplace in Jordan.</p>
        </div>
    </section>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="sticky-sidebar">
                    <nav id="policy-nav" class="nav flex-column sticky-sidebar" data-aos="fade-right">
                        <a class="nav-link sidebar-link active" href="#ai-privacy">AI & Privacy</a>
                        <a class="nav-link sidebar-link" href="#financials">Financial & Commission</a>
                        <a class="nav-link sidebar-link" href="#provider-terms">Provider Conduct</a>
                        <a class="nav-link sidebar-link" href="#safety-dispute">Safety & Dispute</a>
                    </nav>
                </div>
            </div>

            <div class="col-lg-9">

                <div id="ai-privacy" class="policy-card" data-aos="fade-up">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ri-ai-generate step-icon me-3"></i>
                        <h2 class="fw-bold m-0">AI Diagnosis & Privacy</h2>
                    </div>
                    <p>To provide a seamless experience, our system uses advanced Artificial Intelligence to analyze uploaded media. This ensures your service request is accurately categorized without manual entry.</p>
                    <ul>
                        <li>Images and videos are processed to identify the technical nature of the problem.</li>
                        <li>Data is used only for matching you with the correct service provider.</li>
                        <li>All media is encrypted and automatically removed from active storage upon job finalization.</li>
                    </ul>
                </div>

                <div id="financials" class="policy-card" data-aos="fade-up">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ri-wallet-3-line step-icon me-3"></i>
                        <h2 class="fw-bold m-0">Financial & Commission Model</h2>
                    </div>
                    <p>Our platform operates on a 10% commission basis to maintain service quality and support. Depending on the payment method chosen, the following logic applies:</p>

                    <div class="row g-4 mt-2">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 h-100">
                                <h5><i class="ri-visa-line text-primary me-3"></i>Online Payments</h5>
                                <p class="small text-muted">When a user pays via Visa/PayPal, the 10% platform commission is automatically deducted before the funds are released to the provider's wallet.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 h-100 bg-light">
                                <h5><i class="ri-hand-coin-line text-success me-3"></i>Cash Payments</h5>
                                <p class="small text-muted">If a user pays in cash, the provider receives the full amount. The 10% commission is then recorded as a pending balance on the provider's account.</p>
                            </div>
                        </div>
                    </div>

                    <div class="commission-logic-card">
                        <h5 class="fw-bold"><i class="ri-refresh-line me-3"></i>Commission Recovery</h5>
                        <p class="mb-0">The pending commission acts as a debt. It will be automatically deducted from future online payments until cleared.</p>
                    </div>
                </div>

                <div id="provider-terms" class="policy-card" data-aos="fade-up">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ri-user-settings-line step-icon me-3"></i>
                        <h2 class="fw-bold m-0">Provider Obligations</h2>
                    </div>
                    <p>Providers are required to maintain a professional standard. By joining, you agree to:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><i class="ri-check-line me-2 text-primary"></i> Honor the price quotes submitted during the bidding process.</li>
                        <li class="list-group-item"><i class="ri-check-line me-2 text-primary"></i> Ensure in-progress status is updated accurately for user tracking.</li>
                        <li class="list-group-item"><i class="ri-check-line me-2 text-primary"></i> Settle outstanding commission balances to remain active.</li>
                    </ul>
                </div>

                <div id="safety-dispute" class="policy-card" data-aos="fade-up">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ri-shield-flash-line step-icon me-3"></i>
                        <h2 class="fw-bold m-0">Safety & Dispute Resolution</h2>
                    </div>
                    <p>We prioritize the physical and financial safety of our Jordanian community.</p>

                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="dispute-box">
                                <h5 class="fw-bold text-danger"><i class="ri-error-warning-line me-3"></i>Reporting an Issue</h5>
                                <p class="small mb-0">Issues must be reported within 24 hours of job completion. Once payment is released, refunds cannot be processed.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mt-3">Identity & Trust</h6>
                            <p class="small text-muted">All providers undergo email and phone verification.</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mt-3">Legal Compliance</h6>
                            <p class="small text-muted">Illegal activity will be reported to Jordanian authorities.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/policy_scripts.js"></script>
</body>

</html>