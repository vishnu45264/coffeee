<?php
// Include database connection
require_once 'config/database.php';

// Check if the script was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // Get the password - either the default or a custom one
    $password = !empty($_POST['custom_password']) ? $_POST['custom_password'] : 'vishnu45.';
    
    // Hash the password properly
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the admin user's password
    $sql = "UPDATE users SET password = ? WHERE email = 'admin@coffeecafe.com'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Admin password has been updated successfully! You can now log in.";
    } else {
        $error = "Error updating password: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Fix Admin Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="custom_password" class="form-label">Custom Password (Optional)</label>
                                <input type="password" class="form-control" id="custom_password" name="custom_password" 
                                       placeholder="Leave blank to use 'vishnu45.'">
                                <div class="form-text">
                                    If you leave this blank, the password will be set to 'vishnu45.'
                                </div>
                            </div>
                            
                            <button type="submit" name="update" class="btn btn-primary">Update Admin Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 