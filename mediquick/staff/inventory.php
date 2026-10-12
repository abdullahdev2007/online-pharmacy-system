<?php
/**
 * STAFF: MANAGE INVENTORY
 * ---------------------------------------------------------------
 * Two jobs on one page:
 *   1. A table of every existing product where staff can update
 *      price/stock (and now, a product photo) inline.
 *   2. A form at the bottom to add a brand new product, including
 *      an optional photo upload.
 *
 * Product photos are optional — a product with no photo simply shows
 * a "No Image" placeholder on the shop page (see products.php).
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_role('staff');
define('BASE_URL', '../');
$page_title = 'Manage Inventory';
$errors = [];
$success = false;

// --- Add a brand new product ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $dosage_info = trim($_POST['dosage_info']);
    $safety_info = trim($_POST['safety_info']);
    $price = (float)$_POST['price'];
    $stock_qty = (int)$_POST['stock_qty'];
    $requires_prescription = isset($_POST['requires_prescription']) ? 1 : 0;
    $image_path = null; // stays null if no photo is uploaded — that's fine

    if (empty($name)) $errors[] = "Product name is required.";
    if ($price <= 0) $errors[] = "Price must be greater than zero.";

    // Only attempt to handle the photo if one was actually chosen —
    // the file input is optional, so an empty upload isn't an error.
    if (empty($errors) && !empty($_FILES['product_image']['name'])) {
        $file = $_FILES['product_image'];
        $image_error = validate_product_image_upload($file);
        if ($image_error) {
            $errors[] = $image_error;
        } else {
            // Give the file a unique, safe name (never trust the
            // original filename directly — it might contain spaces,
            // odd characters, or even an attempt at a path traversal).
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_name = 'prod_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $dest = '../uploads/products/' . $unique_name;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Store the path relative to the site root, since this
                // same value gets used by pages in different folders
                // (customer/, staff/, and index.php) that each need a
                // slightly different "../" prefix in front of it.
                $image_path = 'uploads/products/' . $unique_name;
            } else {
                $errors[] = "Failed to save the product image. Please try again.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, product_name, description, dosage_info, safety_info, price, stock_qty, requires_prescription, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $name, $description, $dosage_info, $safety_info, $price, $stock_qty, $requires_prescription, $image_path]);
        $success = true;
    }
}

// --- Update stock/price/photo for an EXISTING product ---------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = (int)$_POST['product_id'];
    $stock_qty = (int)$_POST['stock_qty'];
    $price = (float)$_POST['price'];

    // If staff chose a new photo for this row, replace the image_path
    // too; otherwise leave it untouched by simply not including it in
    // the UPDATE at all (two different SQL statements below).
    if (!empty($_FILES['product_image']['name'])) {
        $file = $_FILES['product_image'];
        $image_error = validate_product_image_upload($file);
        if (!$image_error) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_name = 'prod_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $dest = '../uploads/products/' . $unique_name;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $stmt = $pdo->prepare("UPDATE products SET stock_qty = ?, price = ?, image_path = ? WHERE product_id = ?");
                $stmt->execute([$stock_qty, $price, 'uploads/products/' . $unique_name, $product_id]);
                header("Location: inventory.php");
                exit;
            }
        }
    }

    // No new photo (or the photo upload failed silently) — just update
    // stock and price, leaving whatever image_path was already there.
    $stmt = $pdo->prepare("UPDATE products SET stock_qty = ?, price = ? WHERE product_id = ?");
    $stmt->execute([$stock_qty, $price, $product_id]);
    header("Location: inventory.php");
    exit;
}

$products = $pdo->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_name")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Manage Inventory</h1>

<div class="card-box">
    <h2>Current Stock</h2>
    <table class="data-table">
        <thead><tr><th>Photo</th><th>Product</th><th>Category</th><th>Price (Rs.)</th><th>Stock</th><th>Rx?</th><th>Update</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <?php if (!empty($p['image_path'])): ?>
                        <img src="../<?php echo htmlspecialchars($p['image_path']); ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                    <?php else: ?>
                        <span style="color:#94a3b8;font-size:12px;">None</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                <td>
                    <!-- enctype="multipart/form-data" is required here too,
                         since this row's form can now also upload a file. -->
                    <form method="POST" action="inventory.php" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                        <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                        <input type="number" step="0.01" name="price" value="<?php echo $p['price']; ?>" style="width:90px;">
                </td>
                <td>
                        <input type="number" name="stock_qty" value="<?php echo $p['stock_qty']; ?>" style="width:70px; <?php echo $p['stock_qty'] < 20 ? 'border-color:#b3261e;' : ''; ?>">
                </td>
                <td><?php echo $p['requires_prescription'] ? 'Yes' : 'No'; ?></td>
                <td>
                        <input type="file" name="product_image" accept=".jpg,.jpeg,.png,.webp" style="width:120px;font-size:11px;">
                        <button type="submit" name="update_stock" class="btn secondary">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="form-container" style="margin:0;max-width:100%;">
    <h2>Add New Product</h2>
    <?php if ($success): ?><div class="success-box">Product added successfully.</div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div><?php endif; ?>

    <!-- enctype="multipart/form-data" again — required for the photo upload below. -->
    <form method="POST" action="inventory.php" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="product_name" required>

        <label>Category</label>
        <select name="category_id">
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
            <?php endforeach; ?>
        </select>

        <label>Product Photo (optional — JPG, PNG or WEBP, max 3MB)</label>
        <input type="file" name="product_image" accept=".jpg,.jpeg,.png,.webp">

        <label>Description</label>
        <textarea name="description" rows="2"></textarea>

        <label>Dosage Info</label>
        <textarea name="dosage_info" rows="2"></textarea>

        <label>Safety Info</label>
        <textarea name="safety_info" rows="2"></textarea>

        <label>Price (Rs.)</label>
        <input type="number" step="0.01" name="price" required>

        <label>Initial Stock</label>
        <input type="number" name="stock_qty" value="0" required>

        <label><input type="checkbox" name="requires_prescription" style="width:auto;"> Requires Prescription</label>

        <button type="submit" name="add_product" class="btn">Add Product</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
