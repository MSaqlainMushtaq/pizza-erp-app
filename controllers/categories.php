<?php
include __DIR__ . '/../config/db.php';

function getAllCategories($conn) {
    $result = $conn->query("SELECT * FROM categories");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCategoryById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addCategory($conn, $name) {
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    return $stmt->execute();
}

function updateCategory($conn, $id, $name) {
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
    return $stmt->execute();
}

function deleteCategory($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return true;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) { // Foreign key constraint fails
            echo "<script>
                alert('❌ This category cannot be deleted because it is used in products.');
                window.location.href = '/pizza-erp-app/views/categories/index.php';
            </script>";
            exit;
        } else {
            throw $e; // rethrow if another error
        }
    }
}
?>
