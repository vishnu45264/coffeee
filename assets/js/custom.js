// Navbar scroll effect - changes transparency on scroll
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    
    // Add home-page class to body if we're on the homepage
    if (window.location.pathname === '/' || 
        window.location.pathname === '/index.php' || 
        window.location.pathname.endsWith('/cafe/') || 
        window.location.pathname.endsWith('/cafe/index.php')) {
        document.body.classList.add('home-page');
    }
    
    // Function to handle scroll
    function handleScroll() {
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    }
    
    // Add scroll event listener
    window.addEventListener('scroll', handleScroll);
    
    // Run once on page load
    handleScroll();
}); 