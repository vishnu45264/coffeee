    </div><!-- End of content-container -->

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-4"><i class="fas fa-coffee me-2"></i>Coffee Cafe</h5>
                    <p>Serving the finest coffee since 2010. Our mission is to provide an exceptional coffee experience with quality beans and expert brewing.</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="footer-link">Home</a></li>
                        <li><a href="menu.php" class="footer-link">Menu</a></li>
                        <li><a href="gallery.php" class="footer-link">Gallery</a></li>
                        <li><a href="about.php" class="footer-link">About Us</a></li>
                        <li><a href="contact.php" class="footer-link">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="mb-4">Contact Info</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>123 Coffee Street, Bean City, BC 10101</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>(123) 456-7890</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>info@coffeecafe.com</li>
                        <li><i class="fas fa-clock me-2"></i>Mon-Fri: 7am - 10pm, Sat-Sun: 8am - 11pm</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2023 Coffee Cafe. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="footer-link me-3">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS Animation
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    </script>
</body>
</html> 