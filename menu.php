<?php
$pageTitle = 'Menu';
require_once 'include/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if item already in cart
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity']++;
    } else {
        $_SESSION['cart'][$item_id] = [
            'item_id' => $item_id,
            'item_name' => $item_name,
            'item_price' => $item_price,
            'quantity' => 1
        ];
    }

    // Optional: Flash message
    $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        Item added to cart!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';

    // Redirect to avoid form resubmission
    header('Location: menu.php');
    exit();
}

// Get categories for filter
$categories = [];
$sql = "SELECT DISTINCT category FROM menu_items WHERE active = 1 ORDER BY category";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row['category'];
    }
}

// Get menu items
$category_filter = isset($_GET['category']) ? sanitize($_GET['category']) : '';

if (!empty($category_filter)) {
    $sql = "SELECT * FROM menu_items WHERE active = 1 AND category = ? ORDER BY name";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $category_filter);
    mysqli_stmt_execute($stmt);
    $menu_result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM menu_items WHERE active = 1 ORDER BY category, name";
    $menu_result = mysqli_query($conn, $sql);
}
?>

<!-- Menu Hero Section -->
<div class="bg-dark text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold" data-aos="fade-up">Our Menu</h1>
        <p class="lead" data-aos="fade-up" data-aos-delay="100">Discover our delicious coffees and treats</p>
    </div>
</div>

<!-- Menu Content -->
<div class="container py-5">
    <!-- Category Filters -->
    <div class="row mb-4">
        <div class="col-12" data-aos="fade-up">
            <div class="d-flex flex-wrap justify-content-center gap-2 menu-filters">
                <a href="menu.php" class="btn <?php echo empty($category_filter) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                <?php foreach ($categories as $category): ?>
                    <a href="menu.php?category=<?php echo urlencode($category); ?>" 
                       class="btn <?php echo ($category_filter == $category) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <?php echo $category; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Menu Items -->
    <div class="row">
        <?php
        if (mysqli_num_rows($menu_result) > 0) {
            while ($item = mysqli_fetch_assoc($menu_result)) {
                ?>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                    <div class="menu-item">
                        <div class="menu-item-img-container">
                            <img src="assets/img/menu/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="menu-item-img">
                        </div>
                        <div class="menu-item-content">
                            <h5><?php echo $item['name']; ?></h5>
                            <p class="text-muted"><?php echo $item['description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="menu-item-price"><?php echo formatPrice($item['price']); ?></span>
                                <?php if (isLoggedIn()): ?>
                                    <form method="post" action="menu.php" class="d-inline">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['name']); ?>">
                                    <input type="hidden" name="item_price" value="<?php echo $item['price']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-sm btn-success">
                                        <i class="fas fa-plus"></i> Add to Cart
                                    </button>
                                </form>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-sm btn-outline-primary">
                                        Login to Order
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            // No menu items or category empty
            echo '<div class="col-12 text-center py-5">';
            echo '<p class="lead">No items found in this category.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<!-- Special Offers -->
<section class="py-5 bg-light">
    <div class="container" data-aos="fade-up">
        <h2 class="text-center mb-4">Special Offers</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3><i class="fas fa-coffee text-primary mb-3"></i></h3>
                        <h4>Happy Hour</h4>
                        <p>Enjoy 20% off on all hot drinks from 2 PM to 4 PM every weekday.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3><i class="fas fa-birthday-cake text-primary mb-3"></i></h3>
                        <h4>Birthday Special</h4>
                        <p>Get a free coffee on your birthday! Just show your ID to our staff.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customize Your Coffee Section -->
<section class="py-5">
    <div class="container" data-aos="fade-up">
        <h2 class="text-center mb-4">Customize Your Coffee</h2>
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="assets/img/customize-coffee.jpg" alt="Customize Coffee" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <div class="accordion" id="customizeAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Choose Your Bean
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#customizeAccordion">
                            <div class="accordion-body">
                                <p>Select from our premium beans: Ethiopian, Colombian, Brazilian, or our house blend.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Select Milk Type
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#customizeAccordion">
                            <div class="accordion-body">
                                <p>Choose from Whole Milk, Skim Milk, Almond Milk, Soy Milk, or Oat Milk.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Add Flavoring
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#customizeAccordion">
                            <div class="accordion-body">
                                <p>Add vanilla, caramel, hazelnut, chocolate, or cinnamon flavoring to your drink.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'include/footer.php'; ?> 