<?php
// header.php - Global Header Layout Include
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(get_setting('site_name', 'YST Digital')); ?></title>
    
    <!-- Meta Descriptions SEO -->
    <meta name="description" content="Premium Digital Products for Creators. Find high-quality templates, SaaS boilerplate code, Flutter modules, and design kits.">
    <meta name="author" content="YST Digital">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- MDBootstrap UI CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    
    <!-- Custom Style Sheet -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Prevent Light/Dark Theme Flash Script -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
<?php
// Include navigation bars dynamically
include_once __DIR__ . '/navbar.php';
?>
<main class="container py-4 my-2">
    <!-- Display Flash Alerts -->
    <?php echo show_alerts(); ?>
