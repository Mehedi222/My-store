<?php
// admin/includes/header.php - Core Layout Header for Admin Panel
require_once __DIR__ . '/../../config.php';

// Force Admin/Editor Role Security
require_auth(['admin', 'editor']);

$user = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars(get_setting('site_name', 'My Store')); ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- MDBootstrap UI CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    
    <!-- Custom Style Sheet -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .admin-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 250px;
            z-index: 100;
            padding: 24px 0;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-right: 1px solid var(--glass-border);
            overflow-y: auto;
        }
        .admin-main {
            margin-left: 250px;
            padding: 30px;
        }
        @media (max-width: 991.98px) {
            .admin-sidebar {
                display: none;
            }
            .admin-main {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
    
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>

<!-- Desktop Admin Sidebar -->
<aside class="admin-sidebar d-none d-lg-block">
    <div class="px-4 mb-4">
        <a class="d-flex align-items-center text-reset" href="../index.php">
            <img src="../assets/images/logo.svg" alt="My Store" height="36" class="navbar-logo">
        </a>
        <div class="small text-muted mt-1">Admin Dashboard</div>
    </div>
    
    <div class="list-group list-group-light shadow-0 px-3">
        <a href="index.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2 fw-bold">
            <i class="fas fa-chart-line me-3 text-primary"></i>Overview
        </a>
        <a href="products.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
            <i class="fas fa-cubes me-3 text-primary"></i>Manage Products
        </a>
        <a href="orders.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
            <i class="fas fa-box-open me-3 text-primary"></i>Orders &amp; Taxes
        </a>
        <a href="users.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
            <i class="fas fa-users me-3 text-primary"></i>Registered Users
        </a>
        <a href="coupons.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
            <i class="fas fa-ticket-alt me-3 text-primary"></i>Coupons &amp; Deals
        </a>
        <a href="support.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
            <i class="fas fa-headset me-3 text-primary"></i>Inbox &amp; FAQs
        </a>
        <?php if (has_role('admin')): ?>
            <a href="settings.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-4">
                <i class="fas fa-cogs me-3 text-primary"></i>Settings Config
            </a>
        <?php endif; ?>
        
        <hr class="border-color mb-4">
        
        <a href="../profile.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 mb-2">
            <i class="fas fa-id-card me-3 text-muted"></i>My Account
        </a>
        <a href="../logout.php" class="list-group-item list-group-item-action px-3 border-0 rounded-3 text-danger">
            <i class="fas fa-sign-out-alt me-3 text-danger"></i>Logout
        </a>
    </div>
</aside>

<!-- Mobile Navigation Header Banner for Admin Panel -->
<header class="navbar navbar-expand-lg d-lg-none sticky-top py-2 px-3 bg-white" style="background: var(--glass-bg); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border-color); z-index: 100;">
    <div class="container-fluid justify-content-between align-items-center">
        <a class="d-flex align-items-center text-reset fw-bold" href="index.php">
            <img src="../assets/images/logo.svg" alt="My Store" height="30" class="navbar-logo">
        </a>
        <div class="dropdown">
            <button class="btn btn-link text-reset py-1 px-2 border border-color rounded dropdown-toggle shadow-0" type="button" data-mdb-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                <li><a class="dropdown-item" href="index.php"><i class="fas fa-chart-line me-2"></i>Overview</a></li>
                <li><a class="dropdown-item" href="products.php"><i class="fas fa-cubes me-2"></i>Products</a></li>
                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-box-open me-2"></i>Orders</a></li>
                <li><a class="dropdown-item" href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
                <li><a class="dropdown-item" href="coupons.php"><i class="fas fa-ticket-alt me-2"></i>Coupons</a></li>
                <li><a class="dropdown-item" href="support.php"><i class="fas fa-headset me-2"></i>Inbox</a></li>
                <?php if (has_role('admin')): ?>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cogs me-2"></i>Settings</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../index.php"><i class="fas fa-globe me-2"></i>Site Home</a></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<main class="admin-main">
    <div class="container-fluid">
        <!-- Display Flash Alerts -->
        <?php echo show_alerts(); ?>
