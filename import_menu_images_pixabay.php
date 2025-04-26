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

// Pixabay API key - replace with your own API key
$pixabayApiKey = '49947750-20491d4028393d8bc10733643';

// Function to fetch image from Pixabay API based on search term
function fetchImageFromPixabay($searchTerm, $category, $apiKey) {
    // Clean up the search term
    $searchTerm = pathinfo($searchTerm, PATHINFO_FILENAME);
    $searchTerm = str_replace(['-', '_', '.jpg', '.png', '.jpeg'], ' ', $searchTerm);
    
    // Create more specific search based on category
    if (stripos($category, 'coffee') !== false) {
        $searchTerm .= ' coffee';
    } elseif (stripos($category, 'pastry') !== false || stripos($category, 'pastries') !== false) {
        $searchTerm .= ' pastry bakery';
    } elseif (stripos($category, 'dessert') !== false) {
        $searchTerm .= ' dessert';
    }
    
    // Build API URL
    $apiUrl = "https://pixabay.com/api/?key={$apiKey}&q=" . urlencode($searchTerm) . "&image_type=photo&orientation=horizontal&min_width=400&min_height=300&per_page=3";
    
    // Make API request
    $response = file_get_contents($apiUrl);
    
    if ($response === false) {
        return false;
    }
    
    $data = json_decode($response, true);
    
    // Check if hits are available
    if (empty($data['hits'])) {
        return false;
    }
    
    // Get random image from the results
    $randomIndex = mt_rand(0, count($data['hits']) - 1);
    $imageUrl = $data['hits'][$randomIndex]['webformatURL'];
    
    // Download the image
    $imageData = file_get_contents($imageUrl);
    
    if ($imageData === false) {
        return false;
    }
    
    return $imageData;
}

// Function to save image to the server
function saveImage($imageData, $itemName) {
    $uploadDir = 'assets/img/menu/';
    
    // Ensure directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate a unique filename
    $filename = uniqid() . '.jpg';
    
    // Save the image
    if (file_put_contents($uploadDir . $filename, $imageData)) {
        return $filename;
    }
    
    return false;
}

// Alternative function to use Unsplash if Pixabay fails or API key not set
function fetchImageFromUnsplash($searchTerm, $category) {
    // Clean up the search term
    $searchTerm = pathinfo($searchTerm, PATHINFO_FILENAME);
    $searchTerm = str_replace(['-', '_', '.jpg', '.png', '.jpeg'], ' ', $searchTerm);
    
    // Add category to search term
    $searchTerm .= ' ' . $category;
    
    // Build Unsplash source URL
    $searchUrl = "https://source.unsplash.com/400x300/?" . urlencode($searchTerm);
    
    // Get the image data
    $imageData = file_get_contents($searchUrl);
    
    if ($imageData === false) {
        return false;
    }
    
    return $imageData;
}

// Function to process each menu item
function processMenuItem($item, $conn, $apiKey) {
    echo "<p>Processing: {$item['name']} (current image: {$item['image']})</p>";
    
    // Check if the image already exists and is valid
    $imagePath = 'assets/img/menu/' . $item['image'];
    if (file_exists($imagePath) && filesize($imagePath) > 0) {
        echo "<p>Image already exists for {$item['name']}. Skipping.</p>";
        return true;
    }
    
    // Image doesn't exist or is invalid, fetch a new one
    echo "<p>Fetching image for: {$item['name']} ({$item['category']})</p>";
    
    $imageData = null;
    
    // Try Pixabay if API key is provided
    if (!empty($apiKey)) {
        echo "<p>Trying Pixabay API...</p>";
        $imageData = fetchImageFromPixabay($item['name'], $item['category'], $apiKey);
    }
    
    // If Pixabay failed or no API key, try Unsplash
    if (!$imageData) {
        echo "<p>Trying Unsplash API...</p>";
        $imageData = fetchImageFromUnsplash($item['name'], $item['category']);
    }
    
    if (!$imageData) {
        echo "<p>Failed to fetch image for {$item['name']}</p>";
        return false;
    }
    
    // Save the image
    $newFilename = saveImage($imageData, $item['name']);
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
    <title>Import Menu Images (Pixabay)</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .processing-log {
            max-height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
        }
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Import Menu Images (Pixabay API)</h1>
        
        <div class="alert alert-info">
            <strong>Database:</strong> Using <code><?php echo defined('DB_NAME') ? DB_NAME : 'Unknown'; ?></code>
        </div>
        
        <?php if (empty($pixabayApiKey) || $pixabayApiKey == 'YOUR_PIXABAY_API_KEY'): ?>
            <div class="alert alert-warning">
                <strong>Warning:</strong> Pixabay API key is not set. The script will fall back to Unsplash 
                which may provide less accurate results. For better results, get a free API key from 
                <a href="https://pixabay.com/api/docs/" target="_blank">Pixabay</a> and update the $pixabayApiKey variable in this file.
            </div>
        <?php endif; ?>
        
        <?php
        // Get all menu items
        $sql = "SELECT * FROM menu_items";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='alert alert-info'>Found " . mysqli_num_rows($result) . " menu items to process.</div>";
            
            echo "<div class='card p-3 processing-log'>";
            
            $successCount = 0;
            $failCount = 0;
            
            // Process each menu item
            while ($item = mysqli_fetch_assoc($result)) {
                echo "<hr>";
                if (processMenuItem($item, $conn, $pixabayApiKey)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
            
            echo "</div>";
            
            echo "<div class='mt-4 alert alert-success'>Completed! Successfully processed {$successCount} items. Failed: {$failCount}</div>";
            
            // Show updated menu items
            $sql = "SELECT * FROM menu_items ORDER BY category, name";
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                echo "<h3 class='mt-4'>Updated Menu Items</h3>";
                echo "<div class='row'>";
                
                while ($item = mysqli_fetch_assoc($result)) {
                    echo "<div class='col-md-4 mb-3'>";
                    echo "<div class='card'>";
                    echo "<img src='assets/img/menu/{$item['image']}' class='card-img-top' alt='{$item['name']}' style='height: 200px; object-fit: cover;'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>{$item['name']}</h5>";
                    echo "<p class='card-text text-muted'>{$item['category']}</p>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>No menu items found in the database.</div>";
        }
        ?>
        
        <a href="dashboard_admin.php?tab=menu" class="btn btn-primary mt-3">Return to Admin Dashboard</a>
    </div>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 