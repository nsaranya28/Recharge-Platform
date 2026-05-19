<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Theme initialization script (runs immediately to prevent flash of light mode) -->
<script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            document.documentElement.classList.add('dark-mode');
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('dark-mode');
                const btn = document.getElementById('global-theme-btn');
                if (btn) btn.innerHTML = '☀️';
            });
        }
    })();
</script>

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
            
            <li class="nav-item">
                <a href="billers.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'billers.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">💸</span> Billers
                </a>
            </li>

            <li class="nav-item">
                <a href="ev_recharge.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ev_recharge.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🔌</span> EV Recharge
                </a>
            </li>

            <li class="nav-item">
                <a href="tn_services.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tn_services.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏛️</span> TN Services
                </a>
            </li>

            <li class="nav-item">
                <a href="contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📞</span> Contact
                </a>
            </li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard.php?logout=1" class="nav-link" style="color: #ef4444 !important;">
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

            <!-- Theme Toggle Item -->
            <li class="nav-item" style="display: flex; align-items: center; padding: 0.25rem 0.75rem;">
                <button type="button" class="theme-toggle-btn" onclick="toggleGlobalTheme()" id="global-theme-btn" title="Toggle Dark/Light Mode">🌙</button>
            </li>
        </ul>
    </div>
</nav>

<script>
    function toggleGlobalTheme() {
        const isDark = document.body.classList.toggle('dark-mode');
        document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        
        const btn = document.getElementById('global-theme-btn');
        if (btn) {
            btn.innerHTML = isDark ? '☀️' : '🌙';
        }

        // Sync with local page elements (e.g. lucide theme-icon or state variable)
        const localIcon = document.getElementById('theme-icon');
        if (localIcon) {
            localIcon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
            if (window.lucide) window.lucide.createIcons();
        }
        
        // Sync local page states
        if (typeof isDarkMode !== 'undefined') {
            isDarkMode = isDark;
        }
    }

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
            if (menu && menu.classList.contains('active') && !menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.classList.remove('active');
                toggle.classList.remove('is-active');
                document.body.classList.remove('menu-open');
            }
        });

        // Initialize state of button
        const isDarkActive = document.body.classList.contains('dark-mode');
        const btn = document.getElementById('global-theme-btn');
        if (btn) {
            btn.innerHTML = isDarkActive ? '☀️' : '🌙';
        }
    });
</script>
