<?php
// You can set a page title
$pageTitle = 'Navbar Example';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Cafe - <?php echo isset($pageTitle) ? $pageTitle : 'Welcome'; ?></title>
    <style>
        /* Basic page styling */
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Hero section to showcase the transparent navbar */
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
        }

        .hero-title {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Content section */
        .content-section {
            padding: 5rem 0;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .text-center {
            text-align: center;
        }

        .display-4 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .lead {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 1.25rem;
            margin: 2rem auto;
            border: 1px solid transparent;
            border-radius: 0.25rem;
            max-width: 800px;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        pre {
            background-color: #fff;
            padding: 1rem;
            border-radius: 0.25rem;
            overflow: auto;
        }

        code {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
    </style>
</head>
<body>
    <?php 
    // Include just the navbar
    require_once 'include/navbar.php'; 
    ?>

    <!-- Hero Section to showcase transparent navbar -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Transparent Navbar Example</h1>
            <p class="hero-subtitle">Notice how the navbar is transparent at the top and gets a background when you scroll down.</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="text-center">
                <h2 class="display-4">Transparent Navbar Implementation</h2>
                <p class="lead">This page demonstrates the transparent navbar component with scroll effect.</p>
                
                <div class="alert alert-info">
                    <h4>How to Use the Navbar</h4>
                    <p>To include just the navbar on any page, add this code:</p>
                    <pre><code>&lt;?php require_once 'include/navbar.php'; ?&gt;</code></pre>
                    <p>The navbar is transparent by default and gets a background color when you scroll down.</p>
                </div>
                
                <p>Try scrolling up and down to see the navbar transition effect in action.</p>
            </div>
        </div>
    </section>
</body>
</html> 