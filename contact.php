<?php
$pageTitle = 'Contact Us';
require_once 'include/header.php';

// Process form submission
$formSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, process form
    if (empty($errors)) {
        if (isLoggedIn()) {
            // If user is logged in, store in database
            $user_id = $_SESSION['user_id'];
            
            $sql = "INSERT INTO feedback (user_id, message) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                $formSubmitted = true;
            } else {
                $errors[] = "Error: " . mysqli_error($conn);
            }
        } else {
            // For non-logged in users, we're just showing a success message
            // In a real app, you might send an email or store with a guest flag
            $formSubmitted = true;
        }
    }
}
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-4" data-aos="fade-up">Contact Us</h1>
            <p class="lead" data-aos="fade-up" data-aos-delay="100">We'd love to hear from you!</p>
        </div>
    </div>
    
    <div class="row">
        <!-- Contact Information -->
        <div class="col-md-5" data-aos="fade-right">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Reach Out To Us</h3>
                    
                    <div class="d-flex mb-3">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="ms-3">
                            <h5>Address</h5>
                            <p>123 Coffee Street<br>Bean City, BC 10101</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="ms-3">
                            <h5>Phone</h5>
                            <p>(123) 456-7890</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="ms-3">
                            <h5>Email</h5>
                            <p>info@coffeecafe.com</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="ms-3">
                            <h5>Hours</h5>
                            <p>Monday - Friday: 7am - 10pm<br>
                            Saturday - Sunday: 8am - 11pm</p>
                        </div>
                    </div>
                    
                    <div class="social-icons mt-4">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="col-md-7" data-aos="fade-left">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($formSubmitted): ?>
                        <div class="alert alert-success">
                            <h4>Thank you for contacting us!</h4>
                            <p>We have received your message and will get back to you as soon as possible.</p>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number (Optional)</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <select class="form-select" id="subject" name="subject">
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Reservation">Reservation</option>
                                        <option value="Feedback">Feedback</option>
                                        <option value="Order Issue">Order Issue</option>
                                        <option value="Catering">Catering</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map -->
    <div class="row mt-5" data-aos="fade-up">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <h3 class="card-title p-3 mb-0">Find Us</h3>
                    <div class="map-container">
                        <!-- Replace with your actual Google Maps embed code -->
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2989.7333373807397!2d-73.98787252337834!3d40.74844097124!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQ0JzU0LjQiTiA3M8KwNTknMTYuMyJX!5e0!3m2!1sen!2sus!4v1619758965666!5m2!1sen!2sus" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS for Contact Page -->
<style>
    .contact-icon {
        width: 40px;
        height: 40px;
        background-color: #6f4e37;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    .social-icons {
        display: flex;
        gap: 10px;
    }
    
    .social-icon {
        width: 36px;
        height: 36px;
        background-color: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6f4e37;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        background-color: #6f4e37;
        color: white;
    }
    
    .map-container {
        overflow: hidden;
        border-radius: 0 0 4px 4px;
    }
</style>

<?php require_once 'include/footer.php'; ?> 