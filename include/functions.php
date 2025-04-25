<?php
// Start session if not already started
function session_start_safe() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    session_start_safe();
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    session_start_safe();
    return (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
}

// Redirect user based on role
function redirectBasedOnRole() {
    session_start_safe();
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin') {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard_user.php");
        }
        exit;
    }
}

// Sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Show alert message
function showAlert($message, $type = 'success') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}
?> 