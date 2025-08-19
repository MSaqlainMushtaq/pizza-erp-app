<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/sales.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid sale ID.";
    exit;
}

$sale_id = intval($_GET['id']);

// Check if sale exists before attempting deletion
$check = $conn->prepare("SELECT id FROM sales WHERE id = ?");
$check->bind_param("i", $sale_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    echo "Sale not found.";
    exit;
}

if (deleteSale($conn, $sale_id)) {
    header("Location: /pizza-erp-app/views/sales/index.php?success=1");
    exit;
} else {
    echo "Failed to delete sale.";
}
?>
