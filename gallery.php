<?php
$pageTitle = 'Gallery';
require_once 'include/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-4" data-aos="fade-up">Our Gallery</h1>
            <p class="lead" data-aos="fade-up" data-aos-delay="100">Take a visual tour of our coffee shop and delicious offerings</p>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Gallery Images -->
        <div class="col-md-4" data-aos="fade-up">
            <div class="gallery-item">
                <img src="assets/images/gallery/coffee-shop-1.jpg" alt="Coffee Shop Interior" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Our Cozy Interior</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="gallery-item">
                <img src="assets/images/gallery/coffee-2.jpg" alt="Latte Art" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Latte Art</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="gallery-item">
                <img src="assets/images/gallery/pastry-1.jpg" alt="Fresh Pastries" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Fresh Pastries</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up">
            <div class="gallery-item">
                <img src="assets/images/gallery/coffee-beans.jpg" alt="Premium Coffee Beans" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Premium Coffee Beans</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="gallery-item">
                <img src="assets/images/gallery/barista.jpg" alt="Our Expert Barista" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Our Expert Barista</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="gallery-item">
                <img src="assets/images/gallery/coffee-shop-2.jpg" alt="Outside Seating" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Outside Seating</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up">
            <div class="gallery-item">
                <img src="assets/images/gallery/espresso.jpg" alt="Espresso Shot" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Perfect Espresso</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="gallery-item">
                <img src="assets/images/gallery/cake.jpg" alt="Specialty Cake" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Specialty Cakes</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="gallery-item">
                <img src="assets/images/gallery/coffee-3.jpg" alt="Iced Coffee" class="img-fluid rounded">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <h5>Refreshing Iced Coffee</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS for Gallery Effects -->
<style>
    .gallery-item {
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .gallery-item img {
        transition: all 0.4s ease;
    }
    
    .gallery-item:hover img {
        transform: scale(1.05);
    }
    
    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }
    
    .gallery-info {
        text-align: center;
        padding: 0 1rem;
    }
</style>

<?php require_once 'include/footer.php'; ?> 