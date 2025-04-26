<?php
// Check if database connection files exist in different locations
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
} elseif (file_exists('include/db_connect.php')) {
    require_once 'include/db_connect.php';
} elseif (file_exists('db_connect.php')) {
    require_once 'db_connect.php';
} else {
    // Try to locate the files in common directories
    $possible_paths = [
        'includes/db_connect.php',
        '../include/db_connect.php',
        '../includes/db_connect.php',
        '../config/database.php',
        'config/db.php',
    ];
    
    $db_found = false;
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $db_found = true;
            break;
        }
    }
    
    if (!$db_found) {
        die("Error: Could not find database connection files. Please make sure database.php exists in the config directory.");
    }
}

// Load functions
if (file_exists('include/functions.php')) {
    require_once 'include/functions.php';
} elseif (file_exists('functions.php')) {
    require_once 'functions.php';
}

// Ensure only admin can access this page
session_start();
// Temporarily disable admin check for testing
/*
if (!isLoggedIn() || !isAdmin()) {
    die("Unauthorized access");
}
*/

// Function to fetch image from Unsplash API based on search term
function fetchImageFromAPI($searchTerm) {
    // Remove any file extension if present
    $searchTerm = pathinfo($searchTerm, PATHINFO_FILENAME);
    
    // Clean up the search term - remove file extensions, replace dashes/underscores with spaces
    $searchTerm = str_replace(['-', '_', '.jpg', '.png', '.jpeg'], ' ', $searchTerm);
    
    // Create a more specific search term by adding "coffee" or "cafe food" based on category
    $searchUrl = "https://source.unsplash.com/400x300/?" . urlencode($searchTerm);
    
    // Get the image data
    $imageData = file_get_contents($searchUrl);
    
    if ($imageData === false) {
        return false;
    }
    
    return $imageData;
}

// Function to save image to the server
function saveImage($imageData, $menuItem) {
    $uploadDir = 'assets/img/menu/';
    
    // Ensure directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate a unique filename based on the item name
    $filename = uniqid() . '.jpg';
    
    // Save the image
    if (file_put_contents($uploadDir . $filename, $imageData)) {
        return $filename;
    }
    
    return false;
}

// Function to process each menu item
function processMenuItem($item, $conn) {
    echo "<p>Processing: {$item['name']} (current image: {$item['image']})</p>";
    
    // Check if the image already exists and is valid
    $imagePath = 'assets/img/menu/' . $item['image'];
    if (file_exists($imagePath) && filesize($imagePath) > 0) {
        echo "<p>Image already exists for {$item['name']}. Skipping.</p>";
        return true;
    }
    
    // Image doesn't exist or is invalid, fetch a new one
    $searchTerm = $item['name'] . ' ' . $item['category'];
    echo "<p>Fetching image for: {$searchTerm}</p>";
    
    $imageData = fetchImageFromAPI($searchTerm);
    if (!$imageData) {
        echo "<p>Failed to fetch image for {$item['name']}</p>";
        return false;
    }
    
    // Save the image
    $newFilename = saveImage($imageData, $item);
    if (!$newFilename) {
        echo "<p>Failed to save image for {$item['name']}</p>";
        return false;
    }
    
    // Update the database with the new image filename
    $sql = "UPDATE menu_items SET image = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $newFilename, $item['id']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>Successfully updated {$item['name']} with new image: {$newFilename}</p>";
        return true;
    } else {
        echo "<p>Database update failed for {$item['name']}: " . mysqli_error($conn) . "</p>";
        return false;
    }
}

// Start the import process
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Menu Images</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Import Menu Images</h1>
        
        <div class="alert alert-info">
            <strong>Database:</strong> Using <code><?php echo defined('DB_NAME') ? DB_NAME : 'Unknown'; ?></code>
        </div>
        
        <?php
        // Get all menu items
        $sql = "SELECT * FROM menu_items";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='alert alert-info'>Found " . mysqli_num_rows($result) . " menu items to process.</div>";
            
            echo "<div class='card p-3'>";
            
            $successCount = 0;
            $failCount = 0;
            
            // Process each menu item
            while ($item = mysqli_fetch_assoc($result)) {
                echo "<hr>";
                if (processMenuItem($item, $conn)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
            
            echo "</div>";
            
            echo "<div class='mt-4 alert alert-success'>Completed! Successfully processed {$successCount} items. Failed: {$failCount}</div>";
        } else {
            echo "<div class='alert alert-warning'>No menu items found in the database.</div>";
        }
        ?>
        
        <a href="dashboard_admin.php?tab=menu" class="btn btn-primary mt-3">Return to Admin Dashboard</a>
    </div>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 