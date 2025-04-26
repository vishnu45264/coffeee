<?php
$pageTitle = 'Cart';
require_once 'include/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
        Please login to access your cart.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    header("Location: login.php");
    exit;
}

// Update cart item quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = max(1, intval($_POST['quantity']));
    
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] = $quantity;
        $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Cart updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
    
    header('Location: cart.php');
    exit();
}

// Remove item from cart
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        Item removed from cart!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    
    header('Location: cart.php');
    exit();
}

// Clear entire cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        Cart has been cleared!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    
    header('Location: cart.php');
    exit();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    if (!empty($_SESSION['cart'])) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            $user_id = $_SESSION['user_id'];
            $total_price = 0;
            $status = 'pending';
            
            // Calculate total price
            foreach ($_SESSION['cart'] as $item) {
                $total_price += $item['item_price'] * $item['quantity'];
            }
            
            // Insert into orders table
            $sql = "INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ids", $user_id, $total_price, $status);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating order: " . mysqli_error($conn));
            }
            
            // Get the newly created order ID
            $order_id = mysqli_insert_id($conn);
            
            // Insert each cart item into order_details
            foreach ($_SESSION['cart'] as $item) {
                $item_id = $item['item_id'];
                $quantity = $item['quantity'];
                $item_price = $item['item_price'];
                $subtotal = $item_price * $quantity;
                
                $sql = "INSERT INTO order_details (order_id, item_id, quantity, item_price, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iiidd", $order_id, $item_id, $quantity, $item_price, $subtotal);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error adding order details: " . mysqli_error($conn));
                }
            }
            
            // Commit the transaction
            mysqli_commit($conn);
            
            // Clear the cart
            unset($_SESSION['cart']);
            
            $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Order placed successfully! You can track it in your order history.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            
            // Redirect to order history
            header("Location: dashboard_user.php?tab=order_history");
            exit;
            
        } catch (Exception $e) {
            // Roll back if any part failed
            mysqli_rollback($conn);
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error placing order: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            
            header("Location: cart.php");
            exit;
        }
    } else {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            Your cart is empty. Add items before checking out.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        
        header("Location: cart.php");
        exit;
    }
}
?>

<!-- Cart Hero Section -->
<div class="bg-dark text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold" data-aos="fade-up">Your Cart</h1>
        <p class="lead" data-aos="fade-up" data-aos-delay="100">Review your items before checkout</p>
    </div>
</div>

<!-- Cart Content -->
<div class="container py-5">
    <?php if(isset($_SESSION['alert'])): ?>
        <?php echo $_SESSION['alert']; unset($_SESSION['alert']); ?>
    <?php endif; ?>
    
    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="card shadow-sm mb-4" data-aos="fade-up">
            <div class="card-body">
                <h4 class="mb-4">Cart Items</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            foreach ($_SESSION['cart'] as $item_id => $item): 
                                $subtotal = $item['item_price'] * $item['quantity'];
                                $total += $subtotal;
                            ?>
                                <tr>
                                    <td><?php echo $item['item_name']; ?></td>
                                    <td><?php echo formatPrice($item['item_price']); ?></td>
                                    <td>
                                        <form action="cart.php" method="post" class="d-flex align-items-center">
                                            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button type="button" class="btn btn-outline-secondary" onclick="decrementQty(this)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" min="1" value="<?php echo $item['quantity']; ?>" class="form-control text-center" readonly>
                                                <button type="button" class="btn btn-outline-secondary" onclick="incrementQty(this)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <button type="submit" name="update_cart" class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?php echo formatPrice($subtotal); ?></td>
                                    <td>
                                        <a href="cart.php?remove=<?php echo $item_id; ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th><?php echo formatPrice($total); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between" data-aos="fade-up">
            <div>
                <a href="menu.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                </a>
                <a href="cart.php?clear=1" class="btn btn-outline-danger ms-2">
                    <i class="fas fa-trash me-2"></i> Clear Cart
                </a>
            </div>
            <form method="post" action="cart.php">
                <button type="submit" name="checkout" class="btn btn-success">
                    <i class="fas fa-check-circle me-2"></i> Proceed to Checkout
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="text-center py-5" data-aos="fade-up">
            <div class="mb-4">
                <i class="fas fa-shopping-cart fa-5x text-muted"></i>
            </div>
            <h3>Your cart is empty</h3>
            <p class="lead">Add items from our menu to begin your order.</p>
            <a href="menu.php" class="btn btn-primary mt-3">Browse Menu</a>
        </div>
    <?php endif; ?>
</div>

<script>
    function decrementQty(btn) {
        const input = btn.parentNode.querySelector('input[name="quantity"]');
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1;
        }
    }
    
    function incrementQty(btn) {
        const input = btn.parentNode.querySelector('input[name="quantity"]');
        input.value = parseInt(input.value) + 1;
    }
</script>

<?php require_once 'include/footer.php'; ?> 