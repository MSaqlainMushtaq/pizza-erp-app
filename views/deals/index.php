<?php
include_once '../../config/db.php';
require_once '../../includes/auth_check.php';
include_once '../../controllers/deals.php';
include_once '../../includes/header.php';
include_once '../../includes/navbar.php';

// ✅ Allow only admin and manager to access
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager'])) {
    echo "<p style='color:red; padding-left:20px; padding-top:50px;'>Access denied. Only admins and managers can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}

// Fetch all deals and their items
$query = "
    SELECT 
        deals.id AS deal_id,
        deals.name AS deal_name,
        deals.price AS deal_price,
        deal_items.id AS item_id,
        products.name AS product_name,
        deal_items.quantity
    FROM deals
    LEFT JOIN deal_items ON deals.id = deal_items.deal_id
    LEFT JOIN products ON deal_items.product_id = products.id
    WHERE deals.status = 'active'
    ORDER BY deals.name
";
$result = $conn->query($query);

// Group items by deal
$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$row['deal_id']]['deal_name'] = $row['deal_name'];
    $grouped[$row['deal_id']]['deal_price'] = $row['deal_price'];
    $grouped[$row['deal_id']]['items'][] = [
        'item_id' => $row['item_id'],
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity']
    ];
}

// Handle archive request
if (isset($_GET['archive'])) {
    archiveDeal($conn, intval($_GET['archive']));
    header("Location: index.php");
    exit;
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #bfc3ccc8;
        margin: 0;
        padding-top: 50px;
    }
    .top-header {
        text-align: center;
        margin-top: 20px;
        margin-bottom: 10px;
    }
    .top-header img {
        height: 50px;
        vertical-align: middle;
    }
    .top-header h1 {
        display: inline-block;
        margin-left: 10px;
        font-size: 32px;
        color: #ff4500;
        vertical-align: middle;
    }
    .container {
        width: 90%;
        margin: 20px auto;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .header-bar h2 {
        margin: 0;
        color: #333;
    }
    .btn-add {
        background: #28a745;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    .btn-add:hover {
        background: #218838;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        text-align: center;
    }
    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    th {
        background: #ff4500;
        color: #fff;
        text-transform: uppercase;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .btn {
        padding: 8px 14px;
        border-radius: 4px;
        font-size: 14px;
        text-decoration: none;
        color: #fff;
        display: inline-block;
        width: 100px;
        text-align: center;
    }
    .btn-warning {
        background: #ffc107;
        color: #000;
    }
    .btn-warning:hover {
        background: #e0a800;
    }
    .btn-danger {
        background: #dc3545;
    }
    .btn-danger:hover {
        background: #a71d2a;
    }
    .items-list {
        width: 100%;
    }
    .items-list tr td {
        text-align: center;
        padding: 6px 10px;
        font-size: 14px;
    }
    .delete-btn {
        font-size: 12px;
        padding: 4px 8px;
    }
    /* Align Edit/Delete vertically */
    .actions-cell {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 8px;
        height: 100%;
    }
</style>

<!-- Top Logo + Title -->
<div class="top-header">
    <img src="../../assets/images/logo.png" alt="Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="container">
    <div class="header-bar">
        <h2>Deals List</h2>
        <a href="add_deal.php" class="btn-add">+ Add New Deal</a>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">ID</th>
                <th rowspan="2">Deal Name</th>
                <th rowspan="2">Price (Rs.)</th>
                <th colspan="3">Items</th>
                <th rowspan="2">Actions</th>
            </tr>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 1;
            foreach ($grouped as $deal_id => $group):
            ?>
                <tr>
                    <td><?= $index++ ?></td>
                    <td><?= htmlspecialchars($group['deal_name']) ?></td>
                    <td><?= number_format($group['deal_price'], 2) ?></td>
                    <td colspan="3">
                        <table class="items-list">
                            <?php if (!empty($group['items'])): ?>
                                <?php foreach ($group['items'] as $item): ?>
                                    <tr>
                                        <td style="width: 33%;"><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td style="width: 33%;"><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td style="width: 34%;">
                                            <a href="../../controllers/deals.php?delete_item=<?= $item['item_id'] ?>" onclick="return confirm('Delete this item from deal?')" class="btn btn-danger delete-btn">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No items</td></tr>
                            <?php endif; ?>
                        </table>
                    </td>
                    <td class="actions-cell">
                        <a href="edit_deal.php?id=<?= $deal_id ?>" class="btn btn-warning">Edit Deal</a>
                        <a href="?archive=<?= $deal_id ?>" class="btn btn-danger">Delete Deal</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include_once '../../includes/footer.php'; ?>
