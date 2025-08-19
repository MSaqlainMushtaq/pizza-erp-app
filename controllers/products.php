<?php
include __DIR__ . '/../config/db.php';

// Fetch all categories
function getAllCategories($conn) {
    $result = $conn->query("SELECT * FROM categories");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch all products
function getAllProducts($conn) {
    $result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch single product
function getProductById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Add new product
function addProduct($conn, $name, $category_id, $price) {
    $stmt = $conn->prepare("INSERT INTO products (name, category_id, price) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $name, $category_id, $price);
    return $stmt->execute();
}

// Update product
function updateProduct($conn, $id, $name, $category_id, $price) {
    $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, price = ? WHERE id = ?");
    $stmt->bind_param("sidi", $name, $category_id, $price, $id);
    return $stmt->execute();
}

// Delete product with sales check
function deleteProduct($conn, $id) {
    // Check if product is used in sale_items
    $check = $conn->prepare("SELECT COUNT(*) AS total FROM sale_items WHERE product_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        // Return special flag if product is used in sales
        return 'used_in_sales';
    }

    // If not used, delete it
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>
