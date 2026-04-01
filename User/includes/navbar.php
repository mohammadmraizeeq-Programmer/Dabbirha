<link rel="stylesheet" href="../assets/css/navbar.css">
<nav class="glass-nav">
    <a href="../pages/dashboard.php" class="brand-text">
        <i class="bi bi-tools me-2"></i>Dabbirha دبّرها
    </a>
    <div class="ms-auto d-flex align-items-center">
        <span class="d-none d-md-inline text-muted me-3">
            Welcome, <strong><?php echo htmlspecialchars($user_display_name); ?></strong>
        </span>
        <div class="dropdown">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_display_name); ?>&background=008770&color=fff"
                 class="profile-trigger dropdown-toggle" 
                 data-bs-toggle="dropdown" 
                 aria-expanded="false">
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item text-danger" href="../../index.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>