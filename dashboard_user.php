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
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // 1. Insert into orders table (without item_id)
            $sql = "INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ids", $user_id, $total_price, $status);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating order: " . mysqli_error($conn));
            }
            
            // Get the newly created order ID
            $order_id = mysqli_insert_id($conn);
            
            // Get the item price
            $sql_price = "SELECT price FROM menu_items WHERE id = ?";
            $stmt_price = mysqli_prepare($conn, $sql_price);
            mysqli_stmt_bind_param($stmt_price, "i", $item_id);
            mysqli_stmt_execute($stmt_price);
            $result_price = mysqli_stmt_get_result($stmt_price);
            $row_price = mysqli_fetch_assoc($result_price);
            $item_price = $row_price['price'];
            
            // 2. Insert into order_details table
            $sql = "INSERT INTO order_details (order_id, item_id, quantity, item_price, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            $subtotal = $item_price * $quantity;
            mysqli_stmt_bind_param($stmt, "iiidd", $order_id, $item_id, $quantity, $item_price, $subtotal);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error adding order details: " . mysqli_error($conn));
            }
            
            // Commit the transaction
            mysqli_commit($conn);
            
            $_SESSION['alert'] = showAlert("Order placed successfully! You can track it in your order history.");
            
            // Redirect to refresh page
            header("Location: dashboard_user.php");
            exit;
            
        } catch (Exception $e) {
            // Roll back if any part failed
            mysqli_rollback($conn);
            $_SESSION['alert'] = showAlert("Error placing order: " . $e->getMessage(), "danger");
            
            // Redirect to refresh page
            header("Location: dashboard_user.php");
            exit;
        }
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

// Check if the tab is 'order', redirect to cart page instead
if ($active_tab == 'order') {
    header("Location: cart.php");
    exit;
}
?>

<!-- Include the new dashboard CSS -->
<link rel="stylesheet" href="assets/css/dashboard.css">

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title">Coffee Cafe</div>
            <div class="sidebar-subtitle">User Dashboard</div>
        </div>
        
        <div class="sidebar-links">
            <a href="?tab=dashboard" class="sidebar-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
            <a href="?tab=menu" class="sidebar-link <?php echo $active_tab == 'menu' ? 'active' : ''; ?>">
                <i class="fas fa-mug-hot"></i> <span>Menu</span>
            </a>
            <a href="?tab=order_history" class="sidebar-link <?php echo $active_tab == 'order_history' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> <span>Order History</span>
            </a>
            <a href="?tab=feedback" class="sidebar-link <?php echo $active_tab == 'feedback' ? 'active' : ''; ?>">
                <i class="fas fa-comment"></i> <span>Feedback</span>
            </a>
            <a href="?tab=profile" class="sidebar-link <?php echo $active_tab == 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> <span>My Profile</span>
            </a>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <i class="fas fa-user-circle fa-2x"></i>
                <div class="mt-2"><?php echo $_SESSION['name']; ?></div>
            </div>
            <a href="logout.php" class="sidebar-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="dashboard-content">
        <?php if (isset($_SESSION['alert'])): ?>
            <?php echo $_SESSION['alert']; unset($_SESSION['alert']); ?>
        <?php endif; ?>
        
        <?php if ($active_tab == 'dashboard'): ?>
            <!-- Dashboard Overview -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard</h1>
                <div class="welcome-message">Welcome back, <?php echo $_SESSION['name']; ?>!</div>
            </div>
            
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
            
            <div class="stats-row">
                <div class="stats-card" data-aos="fade-up">
                    <div class="stats-icon"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stats-number"><?php echo $total_orders; ?></div>
                    <div class="stats-text">Total Orders</div>
                </div>
                
                <div class="stats-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-icon"><i class="fas fa-clock"></i></div>
                    <div class="stats-number"><?php echo $pending_orders; ?></div>
                    <div class="stats-text">Pending Orders</div>
                </div>
                
                <div class="stats-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stats-number"><?php echo formatPrice($total_spent); ?></div>
                    <div class="stats-text">Total Spent</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4" data-aos="fade-up">
                    <div class="content-card">
                        <div class="card-header">
                            <i class="fas fa-list-alt me-2"></i> Recent Orders
                        </div>
                        <div class="card-body">
                            <?php
                            // Check if the item_id column exists in the orders table
                            $check_column = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'item_id'");
                            $has_item_id = mysqli_num_rows($check_column) > 0;
                            
                            if ($has_item_id) {
                                // Use the original database structure
                                $sql = "SELECT o.*, m.name as item_name FROM orders o 
                                        JOIN menu_items m ON o.item_id = m.id
                                        WHERE o.user_id = ? 
                                        ORDER BY o.order_date DESC LIMIT 5";
                                $stmt = mysqli_prepare($conn, $sql);
                                
                                if ($stmt) {
                                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        echo '<div class="table-responsive">';
                                        echo '<table class="admin-table">';
                                        echo '<thead><tr><th>Item</th><th>Date</th><th>Status</th></tr></thead>';
                                        echo '<tbody>';
                                        
                                        while ($order = mysqli_fetch_assoc($result)) {
                                            $status_class = '';
                                            switch ($order['status']) {
                                                case 'pending':
                                                    $status_class = 'status-pending';
                                                    break;
                                                case 'processing':
                                                    $status_class = 'status-processing';
                                                    break;
                                                case 'completed':
                                                    $status_class = 'status-completed';
                                                    break;
                                                case 'cancelled':
                                                    $status_class = 'status-cancelled';
                                                    break;
                                            }
                                            
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($order['item_name']) . '</td>';
                                            echo '<td>' . date('M d, Y', strtotime($order['order_date'])) . '</td>';
                                            echo '<td><span class="status-badge ' . $status_class . '">' . ucfirst($order['status']) . '</span></td>';
                                            echo '</tr>';
                                        }
                                        
                                        echo '</tbody></table></div>';
                                    } else {
                                        echo '<p class="text-center mb-0">You haven\'t placed any orders yet.</p>';
                                    }
                                } else {
                                    echo '<div class="alert alert-danger">Error preparing database query: ' . mysqli_error($conn) . '</div>';
                                }
                            } else {
                                // Check if order_details table exists
                                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'order_details'");
                                $has_order_details = mysqli_num_rows($check_table) > 0;
                                
                                if ($has_order_details) {
                                    // Use the cart system database structure
                                    $sql = "SELECT o.id, o.order_date, o.status, m.name as item_name
                                            FROM orders o
                                            JOIN order_details od ON o.id = od.order_id
                                            JOIN menu_items m ON od.item_id = m.id
                                            WHERE o.user_id = ?
                                            GROUP BY o.id
                                            ORDER BY o.order_date DESC LIMIT 5";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    
                                    if ($stmt) {
                                        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);
                                        
                                        if (mysqli_num_rows($result) > 0) {
                                            echo '<div class="table-responsive">';
                                            echo '<table class="admin-table">';
                                            echo '<thead><tr><th>Order #</th><th>Date</th><th>Status</th></tr></thead>';
                                            echo '<tbody>';
                                            
                                            while ($order = mysqli_fetch_assoc($result)) {
                                                $status_class = '';
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        $status_class = 'status-pending';
                                                        break;
                                                    case 'processing':
                                                        $status_class = 'status-processing';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'status-completed';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'status-cancelled';
                                                        break;
                                                }
                                                
                                                echo '<tr>';
                                                echo '<td>Order #' . $order['id'] . '</td>';
                                                echo '<td>' . date('M d, Y', strtotime($order['order_date'])) . '</td>';
                                                echo '<td><span class="status-badge ' . $status_class . '">' . ucfirst($order['status']) . '</span></td>';
                                                echo '</tr>';
                                            }
                                            
                                            echo '</tbody></table></div>';
                                        } else {
                                            echo '<p class="text-center mb-0">You haven\'t placed any orders yet.</p>';
                                        }
                                    } else {
                                        echo '<div class="alert alert-danger">Error preparing database query: ' . mysqli_error($conn) . '</div>';
                                    }
                                } else {
                                    // Neither structure is valid
                                    echo '<div class="alert alert-danger">Database structure issue: Could not find order data structure.</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="content-card">
                        <div class="card-header">
                            <i class="fas fa-bullhorn me-2"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-3">
                                <a href="menu.php" class="btn btn-primary">
                                    <i class="fas fa-coffee me-2"></i> View Menu
                                </a>
                                <a href="?tab=feedback" class="btn btn-outline">
                                    <i class="fas fa-comment me-2"></i> Send Feedback
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php elseif ($active_tab == 'menu'): ?>
            <!-- Menu Tab -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Coffee Menu</h1>
                <div>
                    <a href="?tab=order" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i> Place an Order
                    </a>
                </div>
            </div>
            
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
                        echo '<h4 class="mt-4 mb-3 text-primary">' . $current_category . '</h4>';
                        echo '<div class="row">';
                    }
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                        <div class="content-card h-100">
                            <?php if(!empty($item['image'])): ?>
                                <img src="assets/img/menu/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="text-primary"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="small mb-3"><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-accent"><?php echo formatPrice($item['price']); ?></span>
                                    <a href="menu.php" class="btn btn-sm btn-primary">Go to Menu</a>
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
            
        <?php elseif ($active_tab == 'order_history'): ?>
            <!-- Order History Tab -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Order History</h1>
            </div>
            
            <?php
            // First, check if the item_id column exists in the orders table
            $check_column = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'item_id'");
            $has_item_id = mysqli_num_rows($check_column) > 0;
            
            if ($has_item_id) {
                // Use the original database structure (direct item in orders table)
                $sql = "SELECT o.*, m.name as item_name, m.image 
                        FROM orders o 
                        JOIN menu_items m ON o.item_id = m.id 
                        WHERE o.user_id = ? 
                        ORDER BY o.order_date DESC";
                $stmt = mysqli_prepare($conn, $sql);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($result) > 0) {
                        echo '<div class="content-card">';
                        echo '<div class="table-responsive">';
                        echo '<table class="admin-table">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Item</th>';
                        echo '<th>Quantity</th>';
                        echo '<th>Price</th>';
                        echo '<th>Date</th>';
                        echo '<th>Status</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        while ($order = mysqli_fetch_assoc($result)) {
                            $status_class = '';
                            switch ($order['status']) {
                                case 'pending':
                                    $status_class = 'status-pending';
                                    break;
                                case 'processing':
                                    $status_class = 'status-processing';
                                    break;
                                case 'completed':
                                    $status_class = 'status-completed';
                                    break;
                                case 'cancelled':
                                    $status_class = 'status-cancelled';
                                    break;
                            }
                            
                            echo '<tr>';
                            echo '<td class="d-flex align-items-center">';
                            if (!empty($order['image'])) {
                                echo '<img src="assets/img/menu/' . $order['image'] . '" alt="' . htmlspecialchars($order['item_name']) . '" 
                                        class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">';
                            }
                            echo htmlspecialchars($order['item_name']) . '</td>';
                            echo '<td>' . $order['quantity'] . '</td>';
                            echo '<td>' . formatPrice($order['total_price']) . '</td>';
                            echo '<td>' . date('M d, Y H:i', strtotime($order['order_date'])) . '</td>';
                            echo '<td><span class="status-badge ' . $status_class . '">' . ucfirst($order['status']) . '</span></td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="content-card">';
                        echo '<div class="card-body text-center py-5">';
                        echo '<i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>';
                        echo '<p>You have not placed any orders yet.</p>';
                        echo '<a href="menu.php" class="btn btn-primary">Browse Menu</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Error preparing database query: ' . mysqli_error($conn) . '</div>';
                }
            } else {
                // Check if order_details table exists
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'order_details'");
                $has_order_details = mysqli_num_rows($check_table) > 0;
                
                if ($has_order_details) {
                    // Use the cart system database structure
                    $sql = "SELECT o.id, o.user_id, o.order_date, o.total_price, o.status,
                            od.quantity, od.item_price, m.name as item_name, m.image
                            FROM orders o
                            JOIN order_details od ON o.id = od.order_id
                            JOIN menu_items m ON od.item_id = m.id
                            WHERE o.user_id = ?
                            ORDER BY o.order_date DESC";
                    $stmt = mysqli_prepare($conn, $sql);
                    
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        if (mysqli_num_rows($result) > 0) {
                            echo '<div class="content-card">';
                            echo '<div class="table-responsive">';
                            echo '<table class="admin-table">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Item</th>';
                            echo '<th>Quantity</th>';
                            echo '<th>Price</th>';
                            echo '<th>Date</th>';
                            echo '<th>Status</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            while ($order = mysqli_fetch_assoc($result)) {
                                $status_class = '';
                                switch ($order['status']) {
                                    case 'pending':
                                        $status_class = 'status-pending';
                                        break;
                                    case 'processing':
                                        $status_class = 'status-processing';
                                        break;
                                    case 'completed':
                                        $status_class = 'status-completed';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'status-cancelled';
                                        break;
                                }
                                
                                echo '<tr>';
                                echo '<td class="d-flex align-items-center">';
                                if (!empty($order['image'])) {
                                    echo '<img src="assets/img/menu/' . $order['image'] . '" alt="' . htmlspecialchars($order['item_name']) . '" 
                                            class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">';
                                }
                                echo htmlspecialchars($order['item_name']) . '</td>';
                                echo '<td>' . $order['quantity'] . '</td>';
                                echo '<td>' . formatPrice($order['item_price']) . '</td>';
                                echo '<td>' . date('M d, Y H:i', strtotime($order['order_date'])) . '</td>';
                                echo '<td><span class="status-badge ' . $status_class . '">' . ucfirst($order['status']) . '</span></td>';
                                echo '</tr>';
                            }
                            
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo '<div class="content-card">';
                            echo '<div class="card-body text-center py-5">';
                            echo '<i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>';
                            echo '<p>You have not placed any orders yet.</p>';
                            echo '<a href="menu.php" class="btn btn-primary">Browse Menu</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">Error preparing database query: ' . mysqli_error($conn) . '</div>';
                    }
                } else {
                    // Neither structure is valid
                    echo '<div class="alert alert-danger">Database structure issue: Could not find order data structure.</div>';
                }
            }
            ?>
            
        <?php elseif ($active_tab == 'feedback'): ?>
            <!-- Feedback Tab -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Feedback</h1>
            </div>
            
            <div class="content-card">
                <div class="card-header">Send Feedback</div>
                <div class="card-body">
                    <form action="dashboard_user.php?tab=feedback" method="POST" class="dashboard-form">
                        <div class="form-group">
                            <label for="message" class="form-label">Your Feedback:</label>
                            <textarea name="message" id="message" rows="5" class="form-control" required placeholder="We value your opinion! Let us know your thoughts, suggestions, or report any issues."></textarea>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="submit_feedback" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php
            // Get past feedback
            $sql = "SELECT * FROM feedback WHERE user_id = ? ORDER BY feedback_date DESC";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                echo '<div class="content-card mt-4">';
                echo '<div class="card-header">Your Past Feedback</div>';
                echo '<div class="card-body">';
                
                while ($feedback = mysqli_fetch_assoc($result)) {
                    echo '<div class="mb-3 pb-3" style="border-bottom: 1px solid rgba(111, 78, 55, 0.1);">';
                    echo '<div class="small text-muted mb-2">' . date('F d, Y H:i', strtotime($feedback['feedback_date'])) . '</div>';
                    echo '<p class="mb-0">' . nl2br(htmlspecialchars($feedback['message'])) . '</p>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            }
            ?>
            
        <?php elseif ($active_tab == 'profile'): ?>
            <!-- Profile Tab -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">My Profile</h1>
            </div>
            
            <?php
            // Get user details
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            ?>
            
            <div class="content-card">
                <div class="card-header">Profile Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-4 mb-md-0">
                            <div style="width: 120px; height: 120px; background-color: var(--primary-color); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 3rem; margin-bottom: 1rem;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                            <div class="text-muted small">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row mb-3">
                                <div class="col-sm-3 text-primary fw-bold">Full Name:</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['name']); ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3 text-primary fw-bold">Email:</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3 text-primary fw-bold">Member Status:</div>
                                <div class="col-sm-9">
                                    <span class="status-badge status-completed">Active Member</span>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-secondary disabled">
                                    <i class="fas fa-edit me-2"></i> Edit Profile
                                </button>
                                <button type="button" class="btn btn-outline disabled">
                                    <i class="fas fa-key me-2"></i> Change Password
                                </button>
                            </div>
                            
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i> Profile editing is coming soon in a future update!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'include/footer.php'; ?> 