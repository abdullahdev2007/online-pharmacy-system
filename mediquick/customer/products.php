<?php
/**
 * PRODUCT LISTING (SHOP) PAGE
 * ---------------------------------------------------------------
 * Shows every product in the catalogue, with a text search box and
 * a category dropdown that both work together (a visitor can search
 * AND filter by category at the same time). Anyone can view this
 * page, logged in or not — browsing doesn't require an account,
 * only checking out does.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
define('BASE_URL', '../'); // tells header.php how far "up" to reach assets/ from this folder
$page_title = 'Shop';

// Read the search box and category dropdown from the URL (?search=...&category=...).
// Using GET (not POST) for search means the results page has its own
// shareable URL and works correctly with the browser's Back button.
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build the SQL query in pieces, adding a condition only for each
// filter that's actually in use. "WHERE 1=1" is a small trick that
// lets every extra condition start with "AND ..." without having to
// special-case "is this the first condition or not".
$sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
$params = [];

if ($search !== '') {
    // The % wildcards mean "contains this text anywhere in the name",
    // e.g. searching "cet" will match "Cetirizine 10mg".
    $sql .= " AND p.product_name LIKE ?";
    $params[] = "%$search%";
}
if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}
$sql .= " ORDER BY p.product_name ASC";

// Using a prepared statement here (placeholders + execute($params))
// means the search text the user typed can NEVER be interpreted as
// part of the SQL command itself, even if they type something like
// "'; DROP TABLE products; --" into the search box.
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate query for the category dropdown options.
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Our Products</h1>

<!-- GET form: submitting this just changes the URL's query string,
     which is what lets ?search=...&category=... work above. -->
<form method="GET" action="products.php" class="filter-bar">
    <input type="text" name="search" placeholder="Search medicines..." value="<?php echo htmlspecialchars($search); ?>">
    <select name="category">
        <option value="0">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
</form>

<?php if (empty($products)): ?>
    <p>No products match your search.</p>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <!-- If staff have uploaded a photo for this product, show it.
                     Otherwise fall back to a simple placeholder box so the
                     card layout doesn't break for products without a photo. -->
                <?php if (!empty($p['image_path'])): ?>
                    <img src="../<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>" class="product-image">
                <?php else: ?>
                    <div class="product-image-placeholder">No Image</div>
                <?php endif; ?>

                <span class="<?php echo $p['requires_prescription'] ? 'rx-badge' : 'otc-badge'; ?>">
                    <?php echo $p['requires_prescription'] ? 'Prescription Required' : 'OTC'; ?>
                </span>
                <h3><?php echo htmlspecialchars($p['product_name']); ?></h3>
                <p><?php echo htmlspecialchars($p['category_name']); ?></p>
                <p class="price">Rs. <?php echo number_format($p['price'], 2); ?></p>
                <p><?php echo $p['stock_qty'] > 0 ? $p['stock_qty'] . ' in stock' : 'Out of stock'; ?></p>
                <a href="product_detail.php?id=<?php echo $p['product_id']; ?>" class="btn">View Details</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
