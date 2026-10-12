<?php
/**
 * HOMEPAGE
 * ---------------------------------------------------------------
 * Landing page shown at the site's root URL. It's deliberately kept
 * "public" (no require_login) since browsing categories and new
 * arrivals should work for a first-time visitor with no account yet.
 */
require_once 'includes/auth.php';
require_once 'config/db.php';
define('BASE_URL', ''); // this page IS the root, so no "../" is needed
$page_title = 'Home';

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Just the 4 newest products (highest product_id = most recently added),
// not the entire catalogue — keeps the homepage fast to load.
$featured = $pdo->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="card-box" style="text-align:center;">
    <h1>Welcome to MediQuick Pharmacy</h1>
    <p>Your trusted online pharmacy in Kurunegala — medicines, wellness products, and personal care, delivered safely.</p>
    <a href="customer/products.php" class="btn">Shop Now</a>
</div>

<h2>Shop by Category</h2>
<div class="product-grid">
    <?php foreach ($categories as $cat): ?>
        <div class="product-card" style="text-align:center;">
            <h3><?php echo htmlspecialchars($cat['category_name']); ?></h3>
            <a href="customer/products.php?category=<?php echo $cat['category_id']; ?>" class="btn">Browse</a>
        </div>
    <?php endforeach; ?>
</div>

<h2 style="margin-top:30px;">New Arrivals</h2>
<div class="product-grid">
    <?php foreach ($featured as $p): ?>
        <div class="product-card">
            <?php if (!empty($p['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>" class="product-image">
            <?php else: ?>
                <div class="product-image-placeholder">No Image</div>
            <?php endif; ?>
            <span class="<?php echo $p['requires_prescription'] ? 'rx-badge' : 'otc-badge'; ?>">
                <?php echo $p['requires_prescription'] ? 'Prescription Required' : 'OTC'; ?>
            </span>
            <h3><?php echo htmlspecialchars($p['product_name']); ?></h3>
            <p class="price">Rs. <?php echo number_format($p['price'], 2); ?></p>
            <a href="customer/product_detail.php?id=<?php echo $p['product_id']; ?>" class="btn">View Details</a>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
