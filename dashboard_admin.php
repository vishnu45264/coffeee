<?php
$pageTitle = 'Admin Dashboard';
require_once 'include/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['alert'] = showAlert("Please login to access the dashboard.", "warning");
    header("Location: login.php");
    exit;
}

// Check if user is admin, redirect if not
if (!isAdmin()) {
    $_SESSION['alert'] = showAlert("You don't have permission to access this page.", "danger");
    header("Location: dashboard_user.php");
    exit;
}

// Get current active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Process menu item form - Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_menu_item'])) {
    $item_id = isset($_POST['item_id']) ? sanitize($_POST['item_id']) : '';
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $category = sanitize($_POST['category']);
    $is_editing = !empty($item_id);
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($price) || !is_numeric($price) || $price <= 0) $errors[] = "Valid price is required";
    if (empty($category)) $errors[] = "Category is required";
    
    // Process image upload if file is selected
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Invalid file format. Allowed formats: " . implode(', ', $allowed_extensions);
        } else {
            // Generate unique filename
            $image_name = uniqid() . '.' . $file_extension;
            $upload_dir = 'assets/img/menu/';
            
            // Make sure directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($tmp_name, $upload_dir . $image_name)) {
                $errors[] = "Failed to upload image. Please try again.";
            }
        }
    } elseif (!$is_editing) {
        // Image is required for new items
        $errors[] = "Image is required";
    }
    
    // If no validation errors, save to database
    if (empty($errors)) {
        if ($is_editing) {
            // Update existing item
            if (!empty($image_name)) {
                // If new image uploaded, update with new image
                $sql = "UPDATE menu_items SET name = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $price, $category, $image_name, $item_id);
            } else {
                // Keep existing image
                $sql = "UPDATE menu_items SET name = ?, description = ?, price = ?, category = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssdsi", $name, $description, $price, $category, $item_id);
            }
            
            $success_message = "Menu item updated successfully!";
        } else {
            // Add new item
            $active = 1; // New items are active by default
            $sql = "INSERT INTO menu_items (name, description, price, category, image, active) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $price, $category, $image_name, $active);
            
            $success_message = "Menu item added successfully!";
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = showAlert($success_message);
            header("Location: dashboard_admin.php?tab=menu");
            exit;
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}

// Process order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_status'])) {
    $order_id = sanitize($_POST['order_id']);
    $status = sanitize($_POST['status']);
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['alert'] = showAlert("Order status updated successfully!");
    } else {
        $_SESSION['alert'] = showAlert("Error updating order status: " . mysqli_error($conn), "danger");
    }
    
    header("Location: dashboard_admin.php?tab=orders");
    exit;
}

// Delete menu item
if (isset($_GET['delete_item']) && is_numeric($_GET['delete_item'])) {
    $item_id = sanitize($_GET['delete_item']);
    
    // Get image filename to delete the file
    $sql = "SELECT image FROM menu_items WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $image_path = 'assets/img/menu/' . $row['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete from database
    $sql = "DELETE FROM menu_items WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $item_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['alert'] = showAlert("Menu item deleted successfully!");
    } else {
        $_SESSION['alert'] = showAlert("Error deleting menu item: " . mysqli_error($conn), "danger");
    }
    
    header("Location: dashboard_admin.php?tab=menu");
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 dashboard-sidebar">
            <div class="d-flex flex-column p-3">
                <h5 class="mb-4 text-center">Admin Dashboard</h5>
                <a href="?tab=dashboard" class="sidebar-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="?tab=menu" class="sidebar-link <?php echo $active_tab == 'menu' ? 'active' : ''; ?>">
                    <i class="fas fa-coffee"></i> Manage Menu
                </a>
                <a href="?tab=orders" class="sidebar-link <?php echo $active_tab == 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="?tab=feedback" class="sidebar-link <?php echo $active_tab == 'feedback' ? 'active' : ''; ?>">
                    <i class="fas fa-comment"></i> Feedback
                </a>
                <a href="?tab=users" class="sidebar-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="?tab=analytics" class="sidebar-link <?php echo $active_tab == 'analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
                <a href="?tab=settings" class="sidebar-link <?php echo $active_tab == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings
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
                <h2 class="mb-4">Admin Dashboard</h2>
                
                <div class="row mb-4">
                    <?php
                    // Count total users
                    $sql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
                    $result = mysqli_query($conn, $sql);
                    $total_users = mysqli_fetch_assoc($result)['total_users'];
                    
                    // Count total orders
                    $sql = "SELECT COUNT(*) as total_orders FROM orders";
                    $result = mysqli_query($conn, $sql);
                    $total_orders = mysqli_fetch_assoc($result)['total_orders'];
                    
                    // Count pending orders
                    $sql = "SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'";
                    $result = mysqli_query($conn, $sql);
                    $pending_orders = mysqli_fetch_assoc($result)['pending_orders'];
                    
                    // Get total revenue
                    $sql = "SELECT SUM(total_price) as total_revenue FROM orders";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    $total_revenue = $row['total_revenue'] ? $row['total_revenue'] : 0;
                    ?>
                    
                    <div class="col-md-3" data-aos="fade-up">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-users"></i></div>
                            <div class="stats-number"><?php echo $total_users; ?></div>
                            <div class="stats-text">Total Users</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-shopping-cart"></i></div>
                            <div class="stats-number"><?php echo $total_orders; ?></div>
                            <div class="stats-text">Total Orders</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-hourglass-half"></i></div>
                            <div class="stats-number"><?php echo $pending_orders; ?></div>
                            <div class="stats-text">Pending Orders</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-dollar-sign"></i></div>
                            <div class="stats-number"><?php echo formatPrice($total_revenue); ?></div>
                            <div class="stats-text">Total Revenue</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8 mb-4" data-aos="fade-up">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Sales Analytics</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get last 7 days of sales data
                                $sales_data = [];
                                $labels = [];
                                $data = [];
                                
                                for ($i = 6; $i >= 0; $i--) {
                                    $date = date('Y-m-d', strtotime("-$i days"));
                                    $labels[] = date('M d', strtotime("-$i days"));
                                    
                                    $sql = "SELECT SUM(total_price) as daily_sales FROM orders 
                                            WHERE DATE(order_date) = ?";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    mysqli_stmt_bind_param($stmt, "s", $date);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    $row = mysqli_fetch_assoc($result);
                                    
                                    $data[] = $row['daily_sales'] ? round($row['daily_sales'], 2) : 0;
                                }
                                ?>
                                <canvas id="salesChart" height="250"></canvas>
                                
                                <script>
                                    // Sales chart data
                                    const salesChartLabels = <?php echo json_encode($labels); ?>;
                                    const salesChartData = <?php echo json_encode($data); ?>;
                                </script>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Popular Items</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get top 5 popular items
                                $sql = "SELECT m.name, COUNT(o.id) as order_count 
                                        FROM orders o 
                                        JOIN menu_items m ON o.item_id = m.id 
                                        GROUP BY o.item_id 
                                        ORDER BY order_count DESC 
                                        LIMIT 5";
                                $result = mysqli_query($conn, $sql);
                                
                                $item_labels = [];
                                $item_data = [];
                                
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $item_labels[] = $row['name'];
                                        $item_data[] = $row['order_count'];
                                    }
                                }
                                ?>
                                
                                <canvas id="itemsChart" height="250"></canvas>
                                
                                <script>
                                    // Popular items chart data
                                    const itemsChartLabels = <?php echo json_encode($item_labels); ?>;
                                    const itemsChartData = <?php echo json_encode($item_data); ?>;
                                </script>
                            </div>
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
                                $sql = "SELECT o.*, u.name as user_name, m.name as item_name 
                                        FROM orders o 
                                        JOIN users u ON o.user_id = u.id 
                                        JOIN menu_items m ON o.item_id = m.id 
                                        ORDER BY o.order_date DESC LIMIT 5";
                                $result = mysqli_query($conn, $sql);
                                
                                if (mysqli_num_rows($result) > 0) {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-hover">';
                                    echo '<thead><tr><th>Order ID</th><th>User</th><th>Item</th><th>Status</th></tr></thead>';
                                    echo '<tbody>';
                                    
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>';
                                        echo '<td>#' . $row['id'] . '</td>';
                                        echo '<td>' . $row['user_name'] . '</td>';
                                        echo '<td>' . $row['item_name'] . '</td>';
                                        echo '<td>';
                                        if ($row['status'] == 'pending') {
                                            echo '<span class="badge bg-warning">Pending</span>';
                                        } elseif ($row['status'] == 'processing') {
                                            echo '<span class="badge bg-info">Processing</span>';
                                        } elseif ($row['status'] == 'completed') {
                                            echo '<span class="badge bg-success">Completed</span>';
                                        } elseif ($row['status'] == 'cancelled') {
                                            echo '<span class="badge bg-danger">Cancelled</span>';
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
                                    <a href="?tab=orders" class="btn btn-outline-primary">View All Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Recent Feedback</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $sql = "SELECT f.*, u.name as user_name 
                                        FROM feedback f 
                                        JOIN users u ON f.user_id = u.id 
                                        ORDER BY f.feedback_date DESC LIMIT 3";
                                $result = mysqli_query($conn, $sql);
                                
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<div class="mb-3 p-3 bg-light rounded">';
                                        echo '<p class="mb-1">' . nl2br(htmlspecialchars($row['message'])) . '</p>';
                                        echo '<div class="d-flex justify-content-between">';
                                        echo '<small class="text-muted">From: ' . $row['user_name'] . '</small>';
                                        echo '<small class="text-muted">' . date('M d, Y', strtotime($row['feedback_date'])) . '</small>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p class="text-center">No feedback yet.</p>';
                                }
                                ?>
                                <div class="text-center mt-3">
                                    <a href="?tab=feedback" class="btn btn-outline-primary">View All Feedback</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($active_tab == 'menu'): ?>
                <!-- Menu Management Tab -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Menu</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                        <i class="fas fa-plus me-1"></i> Add New Item
                    </button>
                </div>
                
                <!-- Menu Categories Filter -->
                <div class="mb-4">
                    <?php
                    // Get all categories
                    $sql = "SELECT DISTINCT category FROM menu_items ORDER BY category";
                    $result = mysqli_query($conn, $sql);
                    $categories = [];
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $categories[] = $row['category'];
                    }
                    
                    // Current filter
                    $category_filter = isset($_GET['category']) ? sanitize($_GET['category']) : '';
                    ?>
                    
                    <div class="d-flex flex-wrap gap-2">
                        <a href="?tab=menu" class="btn <?php echo empty($category_filter) ? 'btn-primary' : 'btn-outline-primary'; ?>">All Items</a>
                        <?php foreach ($categories as $category): ?>
                            <a href="?tab=menu&category=<?php echo urlencode($category); ?>" 
                               class="btn <?php echo ($category_filter == $category) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <?php echo $category; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Menu Items Table -->
                <div class="card admin-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get menu items with filter if needed
                                    if (!empty($category_filter)) {
                                        $sql = "SELECT * FROM menu_items WHERE category = ? ORDER BY name";
                                        $stmt = mysqli_prepare($conn, $sql);
                                        mysqli_stmt_bind_param($stmt, "s", $category_filter);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);
                                    } else {
                                        $sql = "SELECT * FROM menu_items ORDER BY category, name";
                                        $result = mysqli_query($conn, $sql);
                                    }
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($item = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td>
                                                    <img src="assets/img/menu/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" 
                                                         width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                                </td>
                                                <td><?php echo $item['name']; ?></td>
                                                <td><?php echo $item['category']; ?></td>
                                                <td><?php echo formatPrice($item['price']); ?></td>
                                                <td>
                                                    <?php if ($item['active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-item-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editMenuItemModal"
                                                            data-id="<?php echo $item['id']; ?>"
                                                            data-name="<?php echo $item['name']; ?>"
                                                            data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                                            data-price="<?php echo $item['price']; ?>"
                                                            data-category="<?php echo $item['category']; ?>"
                                                            data-image="<?php echo $item['image']; ?>"
                                                            data-active="<?php echo $item['active']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?tab=menu&delete_item=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this item?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center">No menu items found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Add Menu Item Modal -->
                <div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?tab=menu'); ?>" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add New Menu Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if (!empty($errors) && !isset($_POST['item_id'])): ?>
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                <?php foreach ($errors as $error): ?>
                                                    <li><?php echo $error; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Item Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="price" class="form-label">Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" name="category" 
                                                   list="category-list" required>
                                            <datalist id="category-list">
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category; ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Item Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                        <small class="text-muted">Recommended size: 400x300 pixels. Max file size: 2MB.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="save_menu_item" class="btn btn-primary">Save Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Menu Item Modal -->
                <div class="modal fade" id="editMenuItemModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?tab=menu'); ?>" enctype="multipart/form-data">
                                <input type="hidden" name="item_id" id="edit_item_id">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Menu Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if (!empty($errors) && isset($_POST['item_id'])): ?>
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                <?php foreach ($errors as $error): ?>
                                                    <li><?php echo $error; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="edit_name" class="form-label">Item Name</label>
                                        <input type="text" class="form-control" id="edit_name" name="name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="edit_price" class="form-label">Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="edit_price" name="price" min="0.01" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="edit_category" name="category" 
                                                   list="category-list" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_image" class="form-label">Item Image</label>
                                        <div class="mb-2" id="current_image_preview"></div>
                                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                                        <small class="text-muted">Leave empty to keep current image. Recommended size: 400x300 pixels.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="save_menu_item" class="btn btn-primary">Update Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <script>
                // Edit menu item functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const editButtons = document.querySelectorAll('.edit-item-btn');
                    
                    editButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            // Get data from button attributes
                            const id = this.getAttribute('data-id');
                            const name = this.getAttribute('data-name');
                            const description = this.getAttribute('data-description');
                            const price = this.getAttribute('data-price');
                            const category = this.getAttribute('data-category');
                            const image = this.getAttribute('data-image');
                            
                            // Set form values
                            document.getElementById('edit_item_id').value = id;
                            document.getElementById('edit_name').value = name;
                            document.getElementById('edit_description').value = description;
                            document.getElementById('edit_price').value = price;
                            document.getElementById('edit_category').value = category;
                            
                            // Set current image preview
                            const imagePreview = document.getElementById('current_image_preview');
                            imagePreview.innerHTML = `<img src="assets/img/menu/${image}" alt="${name}" class="img-thumbnail" width="100">
                                                     <p class="mb-0">Current image: ${image}</p>`;
                        });
                    });
                });
                </script>
            <?php elseif ($active_tab == 'orders'): ?>
                <!-- Orders Management Tab -->
                <h2 class="mb-4">Manage Orders</h2>
                
                <!-- Orders Filters -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="btn-group">
                            <a href="?tab=orders" class="btn <?php echo !isset($_GET['status']) ? 'btn-primary' : 'btn-outline-primary'; ?>">All Orders</a>
                            <a href="?tab=orders&status=pending" class="btn <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending</a>
                            <a href="?tab=orders&status=processing" class="btn <?php echo (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'btn-primary' : 'btn-outline-primary'; ?>">Processing</a>
                            <a href="?tab=orders&status=completed" class="btn <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'btn-primary' : 'btn-outline-primary'; ?>">Completed</a>
                            <a href="?tab=orders&status=cancelled" class="btn <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'btn-primary' : 'btn-outline-primary'; ?>">Cancelled</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <form class="d-flex" method="GET">
                            <input type="hidden" name="tab" value="orders">
                            <input type="text" class="form-control me-2" name="search" placeholder="Search orders..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <div class="card admin-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>User</th>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Build query based on filters
                                    $sql_params = [];
                                    $where_clauses = [];
                                    
                                    // Base query
                                    $sql = "SELECT o.*, u.name as user_name, u.email as user_email, m.name as item_name, m.image as item_image 
                                            FROM orders o 
                                            JOIN users u ON o.user_id = u.id 
                                            JOIN menu_items m ON o.item_id = m.id";
                                    
                                    // Status filter
                                    if (isset($_GET['status']) && !empty($_GET['status'])) {
                                        $status = sanitize($_GET['status']);
                                        $where_clauses[] = "o.status = ?";
                                        $sql_params[] = $status;
                                    }
                                    
                                    // Search filter
                                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                                        $search = sanitize($_GET['search']);
                                        $where_clauses[] = "(u.name LIKE ? OR u.email LIKE ? OR m.name LIKE ? OR o.id LIKE ?)";
                                        $search_param = "%$search%";
                                        $sql_params[] = $search_param;
                                        $sql_params[] = $search_param;
                                        $sql_params[] = $search_param;
                                        $sql_params[] = $search_param;
                                    }
                                    
                                    // Add where clause if needed
                                    if (!empty($where_clauses)) {
                                        $sql .= " WHERE " . implode(" AND ", $where_clauses);
                                    }
                                    
                                    // Order by latest first
                                    $sql .= " ORDER BY o.order_date DESC";
                                    
                                    // Prepare and execute
                                    $stmt = mysqli_prepare($conn, $sql);
                                    
                                    if (!empty($sql_params)) {
                                        $types = str_repeat('s', count($sql_params));
                                        mysqli_stmt_bind_param($stmt, $types, ...$sql_params);
                                    }
                                    
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($order = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td>
                                                    <span data-bs-toggle="tooltip" title="<?php echo $order['user_email']; ?>">
                                                        <?php echo $order['user_name']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="assets/img/menu/<?php echo $order['item_image']; ?>" 
                                                             alt="<?php echo $order['item_name']; ?>" class="me-2" 
                                                             width="40" height="40" style="object-fit: cover; border-radius: 4px;">
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
                                                <td>
                                                    <button class="btn btn-sm btn-primary update-status-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#updateStatusModal"
                                                            data-id="<?php echo $order['id']; ?>"
                                                            data-status="<?php echo $order['status']; ?>">
                                                        Update Status
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">No orders found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Update Status Modal -->
                <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?tab=orders'); ?>">
                                <input type="hidden" name="order_id" id="update_order_id">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Order Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="update_status" name="status" required>
                                            <option value="pending">Pending</option>
                                            <option value="processing">Processing</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_order_status" class="btn btn-primary">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <script>
                // Update order status functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const updateButtons = document.querySelectorAll('.update-status-btn');
                    
                    updateButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            // Get data from button attributes
                            const id = this.getAttribute('data-id');
                            const status = this.getAttribute('data-status');
                            
                            // Set form values
                            document.getElementById('update_order_id').value = id;
                            document.getElementById('update_status').value = status;
                        });
                    });
                });
                </script>
                
            <?php elseif ($active_tab == 'feedback'): ?>
                <!-- Feedback Management Tab -->
                <h2 class="mb-4">User Feedback</h2>
                
                <div class="card admin-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Feedback</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT f.*, u.name as user_name, u.email as user_email 
                                            FROM feedback f 
                                            JOIN users u ON f.user_id = u.id 
                                            ORDER BY f.feedback_date DESC";
                                    $result = mysqli_query($conn, $sql);
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($feedback = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td>
                                                    <span data-bs-toggle="tooltip" title="<?php echo $feedback['user_email']; ?>">
                                                        <?php echo $feedback['user_name']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($feedback['feedback_date'])); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="3" class="text-center">No feedback found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'include/footer.php'; ?> 