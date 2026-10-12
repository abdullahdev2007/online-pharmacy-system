<?php
/**
 * SHARED HELPER FUNCTIONS
 * ---------------------------------------------------------------
 * Small reusable pieces of logic used by more than one page:
 *   - cart_* functions   : manage the shopping cart stored in the session
 *   - validate_* functions: check uploaded files before we trust them
 *
 * Keeping these in one place means the cart logic behaves exactly the
 * same whether it's called from the product page, the cart page, or
 * checkout — there's only one copy of the logic to get right.
 */

/**
 * The cart itself is just an array stored in the session:
 *   $_SESSION['cart'] = [ product_id => quantity, product_id => quantity, ... ]
 * We don't create a database table for the cart, because a cart that
 * hasn't been checked out yet isn't really "data" we need to keep
 * long-term — it only needs to exist for as long as the browser
 * session does.
 */

// Add a product to the cart, or increase its quantity if it's already there.
function cart_add($product_id, $qty = 1) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty; // already in cart -> just bump the quantity
    } else {
        $_SESSION['cart'][$product_id] = $qty;  // new item
    }
}

// Remove a product from the cart entirely (used by the "Remove" button).
function cart_remove($product_id) {
    unset($_SESSION['cart'][$product_id]);
}

// Set a product's quantity directly (used when the customer edits the
// quantity box on the cart page and clicks "Update Cart").
function cart_update($product_id, $qty) {
    if ($qty <= 0) {
        cart_remove($product_id); // treat "0 or less" as "remove it"
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }
}

// Empty the cart completely (called right after an order is placed
// successfully, so the next visit starts with a fresh, empty cart).
function cart_clear() {
    $_SESSION['cart'] = [];
}

/**
 * validate_prescription_upload($file)
 * Runs three checks on a file the customer is trying to upload as a
 * prescription, and returns a human-readable error message string if
 * something is wrong, or null if the file is safe to save.
 *
 * We deliberately check the file's REAL content type with
 * mime_content_type() rather than trusting $file['name'] or the
 * browser-supplied type — a bad actor could rename a harmful file to
 * "prescription.jpg" and the filename alone would not catch that.
 */
function validate_prescription_upload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB, in bytes

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "Upload failed. Please try again.";
    }
    if (!in_array(mime_content_type($file['tmp_name']), $allowed_types)) {
        return "Only JPG, PNG, or PDF files are allowed.";
    }
    if ($file['size'] > $max_size) {
        return "File must be smaller than 5MB.";
    }
    return null; // no problems found
}

/**
 * validate_product_image_upload($file)
 * Same idea as above, but for product photos uploaded by staff from
 * the inventory page. Only real image types are allowed (no PDFs
 * here, since this becomes a <img> tag on the shop page), and we
 * use a slightly smaller size limit since these images are shown on
 * a public listing page and we want the page to load quickly.
 */
function validate_product_image_upload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 3 * 1024 * 1024; // 3MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "Image upload failed. Please try again.";
    }
    if (!in_array(mime_content_type($file['tmp_name']), $allowed_types)) {
        return "Only JPG, PNG, or WEBP images are allowed.";
    }
    if ($file['size'] > $max_size) {
        return "Image must be smaller than 3MB.";
    }
    return null;
}
