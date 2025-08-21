<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../controllers/sales.php';
require_once __DIR__ . '/../../includes/header.php';

// ✅ Allow only admin and manager to access
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager'])) {
    echo "<p style='color:red; padding-left:20px; padding-top:50px;'>Access denied. Only admins and managers can delete sale.</p>";
    include_once '../../includes/footer.php';
    exit;
}

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
