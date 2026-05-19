<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Handle logout
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">
            <span class="logo-icon">⚡</span>
            <span class="logo-text">Smart<span>Recharge</span></span>
        </a>
        
        <div class="nav-toggle" id="mobile-menu-toggle">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>

        <ul class="nav-menu" id="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏠</span> Home
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard.php?logout=1" class="nav-link" style="color: #ef4444;">
                        <span class="nav-icon">🚪</span> Logout
                    </a>
                </li>
                <li class="nav-item nav-cta">
                    <a href="dashboard.php" class="nav-btn">My Account</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a href="login.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">
                        Login
                    </a>
                </li>
                <li class="nav-item nav-cta">
                    <a href="register.php" class="nav-btn">Get Started</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('mobile-menu-toggle');
        const menu = document.getElementById('nav-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', function() {
                toggle.classList.toggle('is-active');
                menu.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !toggle.contains(e.target) && menu.classList.contains('active')) {
                menu.classList.remove('active');
                toggle.classList.remove('is-active');
                document.body.classList.remove('menu-open');
            }
        });
    });
</script>
