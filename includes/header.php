
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Navbar</title>

    <link rel="stylesheet" href="assets/css/header_style.css">
</head>

<body>

    <div class="bg-hero-wrapper">
        <header class="header" id="header">
            <nav class="navbar navbar-expand-lg py-3">
                <div class="container">
                    <a href="#" class="navbar-brand fw-bold fs-4"><i class="bi bi-tools me-2"></i>Dabbirha دبّرها</a>

                    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavDropdown">
                        <i class="ri-menu-3-line fs-4"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNavDropdown">
                        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">

                            <li class="nav-item me-lg-3">
                                <a class="nav-link" href="#services">Services</a>
                            </li>
                            <li class="nav-item me-lg-3">
                                <a class="nav-link" href="/Dabbirha/Public/contact.php">Contact</a>
                            </li>
                            <li class="nav-item me-lg-3">
                                <a class="nav-link" href="#about">About</a>
                            </li>
                            <li class="nav-item me-lg-3">
                                <a class="nav-link" href="#how">How It Works</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/Dabbirha/Reg/signup_provider/pages/provider-registration.php">Become a Provider</a>
                            </li>
                        </ul>

                        <div class="d-flex align-items-center gap-3">

                            <a href="/Dabbirha/Reg/signin/pages/signin.php"
                                class="btn rounded-pill sign-in-btn">
                                Sign In
                                <span class="sign-in-arrow">›</span>
                            </a>

                        </div>

                    </div>
                </div>
            </nav>
        </header>