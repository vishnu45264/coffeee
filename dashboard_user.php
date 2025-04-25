<?php
$pageTitle = 'User Dashboard';
require_once 'include/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['alert'] = showAlert("Please login to access the dashboard.", "warning");
    header("Location: login.php");
    exit;
}

// Check if user is admin, redirect if so
if (isAdmin()) {
    header("Location: dashboard_admin.php");
    exit;
}

// Process order form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $item_id = sanitize($_POST['item_id']);
    $quantity = sanitize($_POST['quantity']);
    $total_price = sanitize($_POST['total_price']);
    
    // Validate inputs
    $errors = [];
    if (empty($item_id)) {
        $errors[] = "Item is required";
    }
    if (empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
        $errors[] = "Valid quantity is required";
    }
    if (empty($total_price) || !is_numeric($total_price) || $total_price <= 0) {
        $errors[] = "Valid price is required";
    }
    
    // If no validation errors, insert order
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $status = 'pending';
        
        $sql = "INSERT INTO orders (user_id, item_id, quantity, total_price, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiids", $user_id, $item_id, $quantity, $total_price, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = showAlert("Order placed successfully! You can track it in your order history.");
        } else {
            $_SESSION['alert'] = showAlert("Error placing order: " . mysqli_error($conn), "danger");
        }
        
        // Redirect to refresh page
        header("Location: dashboard_user.php");
        exit;
    }
}

// Process feedback form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $message = sanitize($_POST['message']);
    
    if (!empty($message)) {
        $user_id = $_SESSION['user_id'];
        
        $sql = "INSERT INTO feedback (user_id, message) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = showAlert("Thank you for your feedback!");
        } else {
            $_SESSION['alert'] = showAlert("Error submitting feedback: " . mysqli_error($conn), "danger");
        }
        
        // Redirect to refresh page
        header("Location: dashboard_user.php?tab=feedback");
        exit;
    }
}

// Get current active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Get pre-selected item if coming from menu
$order_item_id = isset($_GET['order']) ? intval($_GET['order']) : 0;
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 dashboard-sidebar">
            <div class="d-flex flex-column p-3">
                <h5 class="mb-4 text-center">User Dashboard</h5>
                <a href="?tab=dashboard" class="sidebar-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="?tab=menu" class="sidebar-link <?php echo $active_tab == 'menu' ? 'active' : ''; ?>">
                    <i class="fas fa-mug-hot"></i> Menu
                </a>
                <a href="?tab=order" class="sidebar-link <?php echo $active_tab == 'order' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Order Now
                </a>
                <a href="?tab=order_history" class="sidebar-link <?php echo $active_tab == 'order_history' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Order History
                </a>
                <a href="?tab=feedback" class="sidebar-link <?php echo $active_tab == 'feedback' ? 'active' : ''; ?>">
                    <i class="fas fa-comment"></i> Feedback
                </a>
                <a href="?tab=profile" class="sidebar-link <?php echo $active_tab == 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="logout.php" class="sidebar-link mt-auto">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 dashboard-content">
            <?php if (isset($_SESSION['alert'])): ?>
                <?php echo $_SESSION['alert']; unset($_SESSION['alert']); ?>
            <?php endif; ?>
            
            <?php if ($active_tab == 'dashboard'): ?>
                <!-- Dashboard Overview -->
                <h2 class="mb-4">Welcome, <?php echo $_SESSION['name']; ?>!</h2>
                
                <div class="row mb-4">
                    <?php
                    // Count total orders
                    $sql = "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $total_orders = mysqli_fetch_assoc($result)['total_orders'];
                    
                    // Count pending orders
                    $sql = "SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status = 'pending'";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $pending_orders = mysqli_fetch_assoc($result)['pending_orders'];
                    
                    // Get total spent
                    $sql = "SELECT SUM(total_price) as total_spent FROM orders WHERE user_id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);
                    $total_spent = $row['total_spent'] ? $row['total_spent'] : 0;
                    ?>
                    
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-shopping-bag"></i></div>
                            <div class="stats-number"><?php echo $total_orders; ?></div>
                            <div class="stats-text">Total Orders</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-clock"></i></div>
                            <div class="stats-number"><?php echo $pending_orders; ?></div>
                            <div class="stats-text">Pending Orders</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-dollar-sign"></i></div>
                            <div class="stats-number"><?php echo formatPrice($total_spent); ?></div>
                            <div class="stats-text">Total Spent</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4" data-aos="fade-up">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $sql = "SELECT o.*, m.name as item_name FROM orders o 
                                        JOIN menu_items m ON o.item_id = m.id
                                        WHERE o.user_id = ? 
                                        ORDER BY o.order_date DESC LIMIT 5";
                                $stmt = mysqli_prepare($conn, $sql);
                                mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                
                                if (mysqli_num_rows($result) > 0) {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-hover">';
                                    echo '<thead><tr><th>Item</th><th>Date</th><th>Status</th></tr></thead>';
                                    echo '<tbody>';
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>';
                                        echo '<td>' . $row['item_name'] . '</td>';
                                        echo '<td>' . date('M d, Y', strtotime($row['order_date'])) . '</td>';
                                        echo '<td>';
                                        if ($row['status'] == 'pending') {
                                            echo '<span class="badge bg-warning">Pending</span>';
                                        } elseif ($row['status'] == 'completed') {
                                            echo '<span class="badge bg-success">Completed</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">' . ucfirst($row['status']) . '</span>';
                                        }
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table></div>';
                                } else {
                                    echo '<p class="text-center">No orders yet.</p>';
                                }
                                ?>
                                <div class="text-center mt-3">
                                    <a href="?tab=order_history" class="btn btn-outline-primary">View All Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Quick Order</h5>
                            </div>
                            <div class="card-body">
                                <p>Ready for your next coffee? Place an order now!</p>
                                <div class="text-center">
                                    <a href="?tab=order" class="btn btn-primary">Order Now</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Special Promotions</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    <h6><i class="fas fa-gift me-2"></i>Special Offer</h6>
                                    <p class="mb-0">Buy 5 coffees, get 1 free! Check your order history to see if you qualify.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($active_tab == 'menu'): ?>
                <!-- Menu Tab -->
                <h2 class="mb-4">Coffee Menu</h2>
                
                <?php
                // Get menu items
                $sql = "SELECT * FROM menu_items WHERE active = 1 ORDER BY category, name";
                $result = mysqli_query($conn, $sql);
                
                if (mysqli_num_rows($result) > 0) {
                    $current_category = '';
                    
                    while ($item = mysqli_fetch_assoc($result)) {
                        // Display category header if new category
                        if ($current_category != $item['category']) {
                            if (!empty($current_category)) {
                                echo '</div>'; // Close previous row
                            }
                            
                            $current_category = $item['category'];
                            echo '<h4 class="mt-4 mb-3">' . $current_category . '</h4>';
                            echo '<div class="row">';
                        }
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                            <div class="menu-item">
                                <img src="assets/img/menu/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="menu-item-img">
                                <div class="menu-item-content">
                                    <h5><?php echo $item['name']; ?></h5>
                                    <p class="text-muted"><?php echo $item['description']; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="menu-item-price"><?php echo formatPrice($item['price']); ?></span>
                                        <a href="?tab=order&item=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-shopping-cart me-1"></i> Order
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    
                    echo '</div>'; // Close last row
                } else {
                    echo '<div class="alert alert-info">No menu items available at the moment.</div>';
                }
                ?>
                
            <?php elseif ($active_tab == 'order'): ?>
                <!-- Order Now Tab -->
                <h2 class="mb-4">Order Now</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?tab=order'); ?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="item_id" class="form-label">Select Item</label>
                                <select class="form-select" id="item_id" name="item_id" required>
                                    <option value="">-- Select a coffee or item --</option>
                                    <?php
                                    // Get menu items grouped by category
                                    $sql = "SELECT * FROM menu_items WHERE active = 1 ORDER BY category, name";
                                    $result = mysqli_query($conn, $sql);
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        $current_category = '';
                                        while ($item = mysqli_fetch_assoc($result)) {
                                            // Display category as optgroup
                                            if ($current_category != $item['category']) {
                                                if (!empty($current_category)) {
                                                    echo '</optgroup>';
                                                }
                                                $current_category = $item['category'];
                                                echo '<optgroup label="' . $current_category . '">';
                                            }
                                            
                                            // Pre-select if coming from menu
                                            $selected = ($item['id'] == $order_item_id) ? 'selected' : '';
                                            
                                            echo '<option value="' . $item['id'] . '" data-price="' . $item['price'] . '" ' . $selected . '>'
                                                . $item['name'] . ' (' . formatPrice($item['price']) . ')'
                                                . '</option>';
                                        }
                                        echo '</optgroup>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="total_display" class="form-label">Total Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control" id="total_display" readonly>
                                        <input type="hidden" id="total_price" name="total_price">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="special_instructions" class="form-label">Special Instructions (Optional)</label>
                                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="place_order" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-1"></i> Place Order
                            </button>
                        </div>
                    </div>
                </form>
                
                <script>
                // Calculate total when quantity or item changes
                document.addEventListener('DOMContentLoaded', function() {
                    const itemSelect = document.getElementById('item_id');
                    const quantityInput = document.getElementById('quantity');
                    const totalDisplay = document.getElementById('total_display');
                    const totalPriceInput = document.getElementById('total_price');
                    
                    function calculateTotal() {
                        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
                        if (selectedOption.value) {
                            const price = parseFloat(selectedOption.getAttribute('data-price'));
                            const quantity = parseInt(quantityInput.value) || 0;
                            const total = price * quantity;
                            
                            totalDisplay.value = total.toFixed(2);
                            totalPriceInput.value = total.toFixed(2);
                        } else {
                            totalDisplay.value = '0.00';
                            totalPriceInput.value = '0.00';
                        }
                    }
                    
                    itemSelect.addEventListener('change', calculateTotal);
                    quantityInput.addEventListener('change', calculateTotal);
                    quantityInput.addEventListener('keyup', calculateTotal);
                    
                    // Calculate initial total
                    calculateTotal();
                });
                </script>
                
            <?php elseif ($active_tab == 'order_history'): ?>
                <!-- Order History Tab -->
                <h2 class="mb-4">Order History</h2>
                
                <?php
                $sql = "SELECT o.*, m.name as item_name, m.image as item_image 
                        FROM orders o 
                        JOIN menu_items m ON o.item_id = m.id
                        WHERE o.user_id = ? 
                        ORDER BY o.order_date DESC";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped admin-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="assets/img/menu/<?php echo $order['item_image']; ?>" alt="<?php echo $order['item_name']; ?>" class="me-2" width="40" height="40" style="object-fit: cover; border-radius: 4px;">
                                                <span><?php echo $order['item_name']; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo $order['quantity']; ?></td>
                                        <td><?php echo formatPrice($order['total_price']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <?php if ($order['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif ($order['status'] == 'processing'): ?>
                                                <span class="badge bg-info">Processing</span>
                                            <?php elseif ($order['status'] == 'completed'): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php elseif ($order['status'] == 'cancelled'): ?>
                                                <span class="badge bg-danger">Cancelled</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo ucfirst($order['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                } else {
                    echo '<div class="alert alert-info">You have not placed any orders yet.</div>';
                }
                ?>
                
            <?php elseif ($active_tab == 'feedback'): ?>
                <!-- Feedback Tab -->
                <h2 class="mb-4">Submit Feedback</h2>
                
                <div class="row">
                    <div class="col-md-8" data-aos="fade-up">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?tab=feedback'); ?>">
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Your Feedback</label>
                                        <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                                        <small class="text-muted">We appreciate your feedback to improve our services.</small>
                                    </div>
                                    <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Your Previous Feedback</h5>
                                <?php
                                $sql = "SELECT * FROM feedback WHERE user_id = ? ORDER BY feedback_date DESC LIMIT 5";
                                $stmt = mysqli_prepare($conn, $sql);
                                mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                
                                if (mysqli_num_rows($result) > 0) {
                                    while ($feedback = mysqli_fetch_assoc($result)) {
                                        echo '<div class="mb-3 p-3 bg-white rounded">';
                                        echo '<p class="mb-1">' . nl2br(htmlspecialchars($feedback['message'])) . '</p>';
                                        echo '<small class="text-muted">Submitted on ' . date('M d, Y', strtotime($feedback['feedback_date'])) . '</small>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p>You have not submitted any feedback yet.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($active_tab == 'profile'): ?>
                <!-- Profile Tab -->
                <h2 class="mb-4">My Profile</h2>
                
                <div class="row">
                    <div class="col-md-6" data-aos="fade-up">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Account Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <p class="form-control"><?php echo $_SESSION['name']; ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <p class="form-control"><?php echo $_SESSION['email']; ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Member Since</label>
                                    <?php
                                    $sql = "SELECT created_at FROM users WHERE id = ?";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    $user = mysqli_fetch_assoc($result);
                                    ?>
                                    <p class="form-control"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                                </div>
                                <a href="#" class="btn btn-primary">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_new_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'include/footer.php'; ?> 