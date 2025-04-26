<?php
// Determine active page
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = isset($pageTitle) ? $pageTitle : '';
?>

<style>
    /* Minimal styling for navbar */
    :root {
        --primary-color: #6F4E37;
        --accent-color: #D4A762;
        --dark-color: #2C1E12;
        --light-color: #F8F3E9;
    }
    
    .cafe-navbar {
        background-color: transparent;
        padding: 15px 0;
        box-shadow: none;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        transition: all 0.3s ease;
    }
    
    .cafe-navbar.scrolled {
        background-color: rgba(44, 30, 18, 0.95);
        padding: 8px 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .cafe-navbar-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .cafe-navbar-brand {
        color: #F8F3E9;
        font-weight: 700;
        font-size: 1.6rem;
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    
    .cafe-navbar-brand:hover {
        color: #D4A762;
    }
    
    .cafe-navbar-toggle {
        display: none;
        background: none;
        border: none;
        color: #F8F3E9;
        font-size: 1.5rem;
        cursor: pointer;
    }
    
    .cafe-navbar-nav {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 10px;
    }
    
    .cafe-nav-link {
        color: #F8F3E9;
        text-decoration: none;
        padding: 8px 15px;
        font-size: 0.95rem;
        position: relative;
        transition: color 0.3s ease;
    }
    
    .cafe-nav-link:hover,
    .cafe-nav-link.active {
        color: #D4A762;
    }
    
    .cafe-nav-link::after {
        content: "";
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: #D4A762;
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }
    
    .cafe-nav-link:hover::after,
    .cafe-nav-link.active::after {
        width: 70%;
    }
    
    .cafe-auth-button {
        background-color: #D4A762;
        color: #2C1E12;
        border-radius: 50px;
        padding: 8px 20px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .cafe-auth-button:hover {
        background-color: #F8F3E9;
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .cafe-navbar-toggle {
            display: block;
        }
        
        .cafe-navbar-nav {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: rgba(44, 30, 18, 0.95);
            padding: 20px;
        }
        
        .cafe-navbar-nav.show {
            display: flex;
        }
    }
</style>

<!-- Navigation -->
<nav class="cafe-navbar" id="cafe-navbar">
    <div class="cafe-navbar-container">
        <a class="cafe-navbar-brand" href="index.php">
            <span style="margin-right: 10px; color: #D4A762;">☕</span>Coffee Cafe
        </a>
        <button class="cafe-navbar-toggle" id="cafe-navbar-toggle">
            ☰
        </button>
        <ul class="cafe-navbar-nav" id="cafe-navbar-nav">
            <li>
                <a class="cafe-nav-link <?php echo ($current_page == 'index' || $pageTitle == 'Welcome') ? 'active' : ''; ?>" href="index.php">Home</a>
            </li>
            <li>
                <a class="cafe-nav-link <?php echo ($current_page == 'menu' || $pageTitle == 'Menu') ? 'active' : ''; ?>" href="menu.php">Menu</a>
            </li>
            <li>
                <a class="cafe-nav-link <?php echo ($current_page == 'gallery' || $pageTitle == 'Gallery') ? 'active' : ''; ?>" href="gallery.php">Gallery</a>
            </li>
            <li>
                <a class="cafe-nav-link <?php echo ($current_page == 'about' || $pageTitle == 'About') ? 'active' : ''; ?>" href="about.php">About</a>
            </li>
            <li>
                <a class="cafe-nav-link <?php echo ($current_page == 'contact' || $pageTitle == 'Contact Us') ? 'active' : ''; ?>" href="contact.php">Contact</a>
            </li>
            <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                <li>
                    <a class="cafe-nav-link" href="<?php echo function_exists('isAdmin') && isAdmin() ? 'dashboard_admin.php' : 'dashboard_user.php'; ?>">Dashboard</a>
                </li>
                <li>
                    <a class="cafe-nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li>
                    <a class="cafe-nav-link <?php echo ($current_page == 'login' || $pageTitle == 'Login') ? 'active' : ''; ?>" href="login.php">Login</a>
                </li>
                <li>
                    <a class="cafe-auth-button" href="register.php">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
    // Add scrolled class immediately if we're not at the top of the page
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('cafe-navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        }
    });
    
    // Simple toggle for mobile menu
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('cafe-navbar-toggle');
        const nav = document.getElementById('cafe-navbar-nav');
        
        if (toggle && nav) {
            toggle.addEventListener('click', function() {
                nav.classList.toggle('show');
            });
        }
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('cafe-navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    });
</script> 