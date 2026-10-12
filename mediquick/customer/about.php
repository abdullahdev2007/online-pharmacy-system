<?php
/**
 * ABOUT US — static public page, no login required.
 * No database queries here at all; the content is fixed text.
 */
require_once '../includes/auth.php';
require_once '../config/db.php';
define('BASE_URL', '../');
$page_title = 'About Us';
include '../includes/header.php';
?>

<div class="card-box">
    <h1>About MediQuick Pharmacy</h1>
    <p>MediQuick Pharmacy is an online pharmacy based in Kurunegala, Sri Lanka, dedicated to making healthcare
    products accessible, affordable, and easy to order. We offer a wide range of prescription medicines,
    over-the-counter drugs, wellness products, and personal care items — all backed by a verified prescription
    review process handled by our qualified pharmacy staff.</p>

    <h2>Our Mission</h2>
    <p>To provide safe, convenient, and reliable access to pharmaceutical products for our community, while
    maintaining the highest standards of accuracy, privacy, and regulatory compliance.</p>

    <h2>Why Choose Us</h2>
    <ul>
        <li>Wide range of medicines and healthcare products</li>
        <li>Secure prescription upload and pharmacist verification</li>
        <li>Fast and reliable delivery across Kurunegala and surrounding areas</li>
        <li>Friendly customer support for all your health-related queries</li>
    </ul>
</div>

<?php include '../includes/footer.php'; ?>
