<?php
/**
 * SHARED SITE HEADER
 * ---------------------------------------------------------------
 * Included at the top of almost every page in the site (after that
 * page's own PHP logic has run, so $page_title etc. are already set).
 * Having ONE header file means the navigation, logo, and <head> tag
 * only need to be written and styled once, and stay identical
 * everywhere the header is used.
 *
 * The navigation links change depending on who's logged in: a
 * customer sees "My Orders"/"My Profile", staff see a link to their
 * dashboard, admins see a link to theirs, and a logged-out visitor
 * sees Login/Register instead. This is all driven by $_SESSION['role'].
 */

// This file assumes session_start() has already run (it's called
// inside includes/auth.php, which every page includes before this file).
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// BASE_URL tells this shared file how many "../" are needed to reach
// assets/ from wherever the CURRENT page actually lives — index.php
// at the root defines it as '', while a page inside customer/ defines
// it as '../', so the same header.php works correctly from any folder.
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>MediQuick Pharmacy</title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?php echo $base; ?>customer/products.php" class="logo">
            <img src="<?php echo $base; ?>assets/img/logo.svg" alt="MediQuick Pharmacy, Kurunegala" height="40">
        </a>
        <nav>
            <a href="<?php echo $base; ?>customer/products.php">Shop</a>
            <a href="<?php echo $base; ?>customer/about.php">About Us</a>
            <a href="<?php echo $base; ?>customer/contact.php">Contact Us</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                <a href="<?php echo $base; ?>customer/upload_prescription.php">Upload Prescription</a>
                <a href="<?php echo $base; ?>customer/dashboard.php">My Orders</a>
                <a href="<?php echo $base; ?>customer/profile.php">My Profile</a>
                <a href="<?php echo $base; ?>customer/cart.php" class="cart-link">Cart (<?php echo $cart_count; ?>)</a>
                <a href="<?php echo $base; ?>customer/logout.php">Logout</a>
            <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'staff'): ?>
                <a href="<?php echo $base; ?>staff/dashboard.php">Staff Dashboard</a>
                <a href="<?php echo $base; ?>customer/logout.php">Logout</a>
            <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?php echo $base; ?>admin/dashboard.php">Admin Dashboard</a>
                <a href="<?php echo $base; ?>customer/logout.php">Logout</a>
            <?php else: ?>
                <!-- Logged-out visitor: still shown a cart link (browsing and
                     adding to cart doesn't require an account — only checkout does). -->
                <a href="<?php echo $base; ?>customer/cart.php" class="cart-link">Cart (<?php echo $cart_count; ?>)</a>
                <a href="<?php echo $base; ?>customer/login.php">Login</a>
                <a href="<?php echo $base; ?>customer/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="site-main">
