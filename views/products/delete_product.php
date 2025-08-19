<?php
include __DIR__ . '/../../controllers/products.php';

if (!isset($_GET['id'])) {
    echo "Product ID missing.";
    exit;
}

$id = $_GET['id'];
$deleteResult = deleteProduct($conn, $id);

if ($deleteResult === true) {
    // Successfully deleted, go to index
    header("Location: index.php");
    exit;
} elseif ($deleteResult === 'used_in_sales') {
    // Show alert and redirect if used in sales
    echo "<script>
        alert('❌ This product cannot be deleted because it is used in sales.');
        window.location.href = 'index.php';
    </script>";
    exit;
} else {
    echo "<script>
        alert('❌ Failed to delete product.');
        window.location.href = 'index.php';
    </script>";
}
?>
