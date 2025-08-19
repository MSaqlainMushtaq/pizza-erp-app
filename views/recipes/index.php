<?php
include __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../includes/header.php';
require_once '../../includes/sidebar.php';

// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    echo "<p style='color:red; padding-left:200px; padding-top:50px;'>Access denied. Only admins can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}


// Fetch all recipes with product + ingredient names + unit names
$query = "
    SELECT 
        recipes.id, 
        recipes.product_id, 
        products.name AS product, 
        ingredients.name AS ingredient, 
        recipes.quantity_required,
        units.base_unit AS base_unit
    FROM recipes 
    JOIN products ON recipes.product_id = products.id 
    JOIN ingredients ON recipes.ingredient_id = ingredients.id
    JOIN units ON recipes.unit_id = units.id
    ORDER BY products.name
";
$result = $conn->query($query);

// Group by product
$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$row['product_id']]['product'] = $row['product'];
    $grouped[$row['product_id']]['recipes'][] = $row;
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #bfc3ccc8;
        margin: 0;
        padding-top: 50px;
        padding-left: 160px;
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
    /* Ingredient delete button - deals style */
    .delete-btn {
        padding: 4px 8px;
        font-size: 12px;
    }
    .delete-btn:hover {
        background-color: #cc0000;
    }
    
    .ingredients-list {
        width: 100%;
    }
    .ingredients-list tr td {
        text-align: center;
        padding: 6px 10px;
        font-size: 14px;
    }
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
        <h2>Recipe List</h2>
        <a href="add_recipe.php" class="btn-add">+ Add New Recipe</a>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">ID</th>
                <th rowspan="2">Product</th>
                <th colspan="3">Ingredients</th>
                <th rowspan="2">Actions</th>
            </tr>
            <tr>
                <th>Name</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 1;
            foreach ($grouped as $product_id => $group):
            ?>
                <tr>
                    <td><?= $index++ ?></td>
                    <td><?= htmlspecialchars($group['product']) ?></td>
                    <td colspan="3">
                        <table class="ingredients-list">
                            <?php foreach ($group['recipes'] as $r): ?>
                                <tr>
                                    <td style="width: 33%;"><?= htmlspecialchars($r['ingredient']) ?></td>
                                    <td style="width: 33%;"><?= htmlspecialchars($r['quantity_required']) ?> <?= htmlspecialchars($r['base_unit']) ?></td>
                                    <td style="width: 34%;">
                                        <a href="../../controllers/recipes.php?delete=<?= $r['id'] ?>" 
                                           onclick="return confirm('Delete this ingredient from recipe?')" 
                                           class="btn btn-danger delete-btn">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td class="actions-cell ">
                        <a href="edit_recipe.php?product_id=<?= $product_id ?>" class="btn btn-warning">Edit Recipe</a>
                        <a href="../../controllers/recipes.php?delete_all=<?= $product_id ?>" 
                           onclick="return confirm('Are you sure you want to delete this entire recipe?')" 
                           class="btn bth-danger">Delete Recipe</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
