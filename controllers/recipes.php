<?php
include __DIR__ . '/../config/db.php';

// Add new recipe
if (isset($_POST['add'])) {
    $product_id = $_POST['product_id'];
    $ingredient_ids = $_POST['ingredient_id'] ?? [];
    $quantities = $_POST['quantity_required'] ?? [];
    $unit_ids = $_POST['unit_id'] ?? [];

    if (count($ingredient_ids) > 0) {
        for ($i = 0; $i < count($ingredient_ids); $i++) {
            $ingredient_id = $ingredient_ids[$i];
            $quantity_required = $quantities[$i];
            $unit_id = $unit_ids[$i];

            if (!empty($ingredient_id) && !empty($quantity_required) && !empty($unit_id)) {
                $stmt = $conn->prepare("INSERT INTO recipes (product_id, ingredient_id, quantity_required, unit_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iidi", $product_id, $ingredient_id, $quantity_required, $unit_id);
                $stmt->execute();
            }
        }
    }
    header("Location: /pizza-erp-app/views/recipes/index.php");
    exit;
}

// Update recipe
if (isset($_POST['update'])) {
    $product_id = $_POST['product_id'];
    $ingredient_ids = $_POST['ingredient_id'] ?? [];
    $quantities = $_POST['quantity_required'] ?? [];
    $unit_ids = $_POST['unit_id'] ?? [];

    // Delete old recipe for product
    $stmt_del = $conn->prepare("DELETE FROM recipes WHERE product_id = ?");
    $stmt_del->bind_param("i", $product_id);
    $stmt_del->execute();

    if (count($ingredient_ids) > 0) {
        for ($i = 0; $i < count($ingredient_ids); $i++) {
            $ingredient_id = $ingredient_ids[$i];
            $quantity_required = $quantities[$i];
            $unit_id = $unit_ids[$i];

            if (!empty($ingredient_id) && !empty($quantity_required) && !empty($unit_id)) {
                $stmt = $conn->prepare("INSERT INTO recipes (product_id, ingredient_id, quantity_required, unit_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iidi", $product_id, $ingredient_id, $quantity_required, $unit_id);
                $stmt->execute();
            }
        }
    }
    header("Location: /pizza-erp-app/views/recipes/index.php");
    exit;
}

// Delete single ingredient from recipe
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: /pizza-erp-app/views/recipes/index.php");
    exit;
}

// Delete entire recipe for a product
if (isset($_GET['delete_all'])) {
    $product_id = intval($_GET['delete_all']);
    $stmt = $conn->prepare("DELETE FROM recipes WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    header("Location: /pizza-erp-app/views/recipes/index.php");
    exit;
}
