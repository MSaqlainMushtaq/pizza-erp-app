<?php
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../includes/auth_check.php';
include __DIR__ . '/../../controllers/products.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];

    if (updateProduct($conn, $id, $name, $category_id, $price)) {
        // Redirect back to product list without alert
        header("Location: index.php");
        exit;
    } else {
        // Redirect to product list if failed too (optional)
        header("Location: index.php");
        exit;
    }
} else {
    // If accessed directly, go back to product list
    header("Location: index.php");
    exit;
}
