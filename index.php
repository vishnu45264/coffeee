<?php
$pageTitle = 'Welcome';
require_once 'include/header.php';
?>

<!-- Hero Section with Full Screen Background -->
<section class="hero-section-home">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content" data-aos="fade-up">
            <h1 class="hero-title">Welcome to Coffee Cafe</h1>
            <p class="hero-subtitle">Experience the finest coffee in town</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="menu.php" class="btn btn-primary btn-lg">View Menu</a>
                <a href="about.php" class="btn btn-outline-light btn-lg">About Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Featured Drinks</h2>
            <p class="lead">Try our most popular coffee selections</p>
        </div>
        
        <div class="row">
            <?php
            // Get featured menu items (4 items)
            $sql = "SELECT * FROM menu_items WHERE active = 1 LIMIT 4";
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo 100 * $row['id']; ?>">
                        <div class="feature-card">
                            <img src="assets/img/menu/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="feature-img">
                            <div class="feature-text">
                                <h5><?php echo $row['name']; ?></h5>
                                <p class="text-muted"><?php echo substr($row['description'], 0, 70) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary"><?php echo formatPrice($row['price']); ?></span>
                                    <a href="menu.php" class="btn btn-sm btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                // If no menu items yet, display placeholders
                for ($i = 1; $i <= 4; $i++) {
                    ?>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo 100 * $i; ?>">
                        <div class="feature-card">
                            <img src="assets/img/placeholder.jpg" alt="Coffee" class="feature-img">
                            <div class="feature-text">
                                <h5>Featured Coffee <?php echo $i; ?></h5>
                                <p class="text-muted">A delicious coffee blend you will love...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">$4.99</span>
                                    <a href="menu.php" class="btn btn-sm btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- About Us Preview -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="assets/img/cafe-interior.jpg" alt="Cafe Interior" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="display-6 fw-bold mb-4">Our Coffee Story</h2>
                <p class="lead">At Coffee Cafe, we're passionate about serving the best coffee experience to our customers.</p>
                <p>Founded in 2010, our cafe has been a beloved spot for coffee lovers. We source our beans from ethical farms around the world, ensuring both quality and sustainability.</p>
                <p>Our trained baristas craft each cup with precision and care, guaranteeing a perfect coffee experience every time.</p>
                <a href="about.php" class="btn btn-primary mt-3">Learn More About Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">What Our Customers Say</h2>
            <p class="lead">Hear from our satisfied coffee lovers</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">The best coffee I've ever had! The atmosphere is cozy and the staff are very friendly. I come here every morning before work.</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="flex-shrink-0">
                                <img src="assets/img/testimonials/person1.jpg" alt="Customer" class="rounded-circle" width="50" height="50">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Regular Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">Their cappuccino is simply perfect! I also love the pastries they serve. The cafe has a great ambiance for working or meeting friends.</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="flex-shrink-0">
                                <img src="assets/img/testimonials/person2.jpg" alt="Customer" class="rounded-circle" width="50" height="50">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Michael Davis</h6>
                                <small class="text-muted">Coffee Enthusiast</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="card-text">I've tried many coffee shops in town, but Coffee Cafe remains my favorite. The quality is consistent, and they remember my usual order!</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="flex-shrink-0">
                                <img src="assets/img/testimonials/person3.jpg" alt="Customer" class="rounded-circle" width="50" height="50">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Emma Wilson</h6>
                                <small class="text-muted">Local Resident</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'include/footer.php'; ?> 