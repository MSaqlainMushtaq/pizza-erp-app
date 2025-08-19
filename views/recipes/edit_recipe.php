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


// Expect product_id (or id) in query string
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($product_id <= 0) {
    echo "<p style='color:red;'>Invalid Product ID.</p>";
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

// Fetch product
$product = $conn->query("SELECT * FROM products WHERE id = {$product_id}")->fetch_assoc();
if (!$product) {
    echo "<p style='color:red;'>Product not found.</p>";
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

// Fetch existing recipe rows for this product
$recipeRows = $conn->query("SELECT * FROM recipes WHERE product_id = {$product_id} ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch lists for dropdowns
$products = $conn->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC);

$ingredientList = [];
$res = $conn->query("SELECT * FROM ingredients ORDER BY name ASC");
while ($r = $res->fetch_assoc()) { $ingredientList[] = $r; }

$unitList = [];
$res2 = $conn->query("SELECT * FROM units ORDER BY base_unit ASC");
while ($r2 = $res2->fetch_assoc()) { $unitList[] = $r2; }

// Handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // product_id from hidden input
    $pid = intval($_POST['product_id']);

    // Basic validation
    $ing_ids = isset($_POST['ingredient_id']) ? $_POST['ingredient_id'] : [];
    $qtys = isset($_POST['quantity_required']) ? $_POST['quantity_required'] : [];
    $unit_ids = isset($_POST['unit_id']) ? $_POST['unit_id'] : [];

    // Remove existing rows for this product (simple approach)
    $del = $conn->prepare("DELETE FROM recipes WHERE product_id = ?");
    $del->bind_param("i", $pid);
    $del->execute();

    // Insert new rows
    $ins = $conn->prepare("INSERT INTO recipes (product_id, ingredient_id, quantity_required, unit_id) VALUES (?, ?, ?, ?)");
    foreach ($ing_ids as $idx => $ing) {
        $ingredient_id = intval($ing);
        $quantity = isset($qtys[$idx]) ? floatval($qtys[$idx]) : 0;
        $unit_id = isset($unit_ids[$idx]) ? intval($unit_ids[$idx]) : 0;

        // skip incomplete rows
        if ($ingredient_id <= 0 || $unit_id <= 0) continue;

        $ins->bind_param("iidi", $pid, $ingredient_id, $quantity, $unit_id);
        $ins->execute();
    }

    header("Location: index.php");
    exit;
}
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #f7f8fa;
        margin: 0;
        padding-top: 50px;
        padding-left: 160px;
    }
    .header-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    .header-logo img { height: 60px; margin-right: 10px; }
    .header-logo h1 { font-size: 32px; font-weight: bold; color: #ff4500; margin: 0; }

    .page-container { max-width: 800px; margin: 40px auto; padding: 20px; }
    .form-card {
        background: #fff; padding: 30px; border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .form-card h2 {
        text-align: center; font-size: 22px; margin-bottom: 25px;
        font-weight: bold; color: #333;
    }

    .form-group { margin-bottom: 18px; }
    .form-group label { display:block; font-weight:bold; margin-bottom:8px; color:#333; }
    .form-group select, .form-group input {
        width:100%; padding:12px; font-size:16px; border:1px solid #ccc;
        border-radius:6px; outline:none; transition: border .3s;
    }
    .form-group select:focus, .form-group input:focus { border-color:#ff4500; }

    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none; margin: 0;
    }
    input[type=number] { -moz-appearance: textfield; }

    .ingredient-headers { display:flex; gap:15px; margin-bottom:8px; font-weight:bold; }
    .ingredient-headers > div { flex:1; }
    .ingredient-headers > div:last-child { width:50px; }

    .ingredient-row { display:flex; gap:15px; margin-bottom:10px; align-items:center; }
    .ingredient-row select, .ingredient-row input { flex:1; }
    .ingredient-row > select:first-child { flex:1; } /* ingredient */
    .ingredient-row > input { flex:1; } /* quantity */
    .ingredient-row > select:last-child { flex:1; } /* unit */

    .btn-remove { background:#dc3545; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:bold; }
    .btn-remove:hover { background:#a71d2a; }
    .btn-add { background:#28a745; color:#fff; padding:10px 18px; border-radius:6px; font-weight:bold; border:none; cursor:pointer; }
    .btn-add:hover { background:#218838; }
    .btn-submit {
        width:100%; background:#ff4500; color:#fff; font-size:18px; padding:14px; border:none; border-radius:8px;
        font-weight:bold; cursor:pointer; transition: background .3s ease;
    }
    .btn-submit:hover { background:#e03e00; }

    .select2-container { width:100% !important; z-index: 9999; }
    .select2-dropdown { z-index: 99999; }

    .back-link { display:block; text-align:center; margin-top:15px; color:#007bff; font-weight:bold; text-decoration:none; }
    .back-link:hover { color:#0056b3; text-decoration:none; }
</style>

<div class="header-logo">
    <img src="../../assets/images/logo.png" alt="Hot Slice Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="page-container">
    <div class="form-card">
        <h2>Edit Recipe</h2>

        <form method="POST">
            <!-- hidden product_id for submission -->
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">

            <div class="form-group" style="width:50%;">
                <label>Product</label>
                <!-- disabled select for readonly appearance; hidden field above for actual submission -->
                <select class="searchable" disabled>
                    <option value=""><?= htmlspecialchars($product['name']) ?></option>
                </select>
            </div>

            <h4 style="margin-top:25px;">Ingredients</h4>

            <div class="ingredient-headers" style="display:flex; gap:15px; margin-bottom:8px; font-weight:bold;">
                <div style="flex:1;">Ingredient</div>
                <div style="flex:1;">Quantity</div>
                <div style="flex:1;">Unit</div>
                <div style="width:50px;"></div>
            </div>

            <div id="ingredient-container">
                <?php if (!empty($recipeRows)): ?>
                    <?php foreach ($recipeRows as $row): ?>
                        <div class="ingredient-row">
                            <select name="ingredient_id[]" class="searchable" required>
                                <option value="">-- Select Ingredient --</option>
                                <?php foreach ($ingredientList as $ing): ?>
                                    <option value="<?= $ing['id'] ?>" <?= ($ing['id'] == $row['ingredient_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ing['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="number" step="0.01" name="quantity_required[]" value="<?= htmlspecialchars($row['quantity_required']) ?>" required>

                            <select name="unit_id[]" class="searchable" required>
                                <option value="">-- Select Unit --</option>
                                <?php foreach ($unitList as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($u['id'] == $row['unit_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['base_unit']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="button" class="btn-remove" onclick="removeRow(this)">✖</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- empty row if no data -->
                    <div class="ingredient-row">
                        <select name="ingredient_id[]" class="searchable" required>
                            <option value="">-- Select Ingredient --</option>
                            <?php foreach ($ingredientList as $ing): ?>
                                <option value="<?= $ing['id'] ?>"><?= htmlspecialchars($ing['name']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" step="0.01" name="quantity_required[]" placeholder="Quantity" required>

                        <select name="unit_id[]" class="searchable" required>
                            <option value="">-- Select Unit --</option>
                            <?php foreach ($unitList as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['base_unit']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button type="button" class="btn-remove" onclick="removeRow(this)">✖</button>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top:10px;">
                <button type="button" class="btn-add" id="add-row">+ Add Ingredient</button>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-submit">Update Recipe</button>
            </div>

            <a href="index.php" class="back-link">← Back to Recipes</a>
        </form>
    </div>
</div>

<!-- Hidden template for a clean new row (prevents copying selected values) -->
<template id="ingredient-template">
    <div class="ingredient-row">
        <select name="ingredient_id[]" class="searchable" required>
            <option value="">-- Select Ingredient --</option>
            <?php foreach ($ingredientList as $ing): ?>
                <option value="<?= $ing['id'] ?>"><?= htmlspecialchars($ing['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="number" step="0.01" name="quantity_required[]" placeholder="Quantity" required>

        <select name="unit_id[]" class="searchable" required>
            <option value="">-- Select Unit --</option>
            <?php foreach ($unitList as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['base_unit']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="button" class="btn-remove" onclick="removeRow(this)">✖</button>
    </div>
</template>

<!-- jQuery + Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize select2 for all present searchable selects
    $('.searchable').select2({
        placeholder: "-- Select --",
        allowClear: true,
        width: '100%'
    });
});

// Add a fresh new row from template (clean, no copied selected values)
document.getElementById('add-row').addEventListener('click', function() {
    const tpl = document.getElementById('ingredient-template');
    const clone = tpl.content.cloneNode(true);
    const container = document.getElementById('ingredient-container');

    // Append clone
    container.appendChild(clone);

    // Initialize select2 on the newly added selects
    // Find last added .ingredient-row
    const rows = container.querySelectorAll('.ingredient-row');
    const lastRow = rows[rows.length - 1];
    $(lastRow).find('.searchable').select2({
        placeholder: "-- Select --",
        allowClear: true,
        width: '100%'
    });
});

function removeRow(btn) {
    const container = document.getElementById('ingredient-container');
    const rows = container.querySelectorAll('.ingredient-row');
    if (rows.length > 1) {
        btn.closest('.ingredient-row').remove();
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
