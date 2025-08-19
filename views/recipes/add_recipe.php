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


// Fetch products, ingredients, units
$products = $conn->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC);
$ingredients = $conn->query("SELECT * FROM ingredients")->fetch_all(MYSQLI_ASSOC);
$units = $conn->query("SELECT * FROM units")->fetch_all(MYSQLI_ASSOC);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $ingredient_ids = $_POST['ingredient_id'];
    $quantities = $_POST['quantity_required'];
    $unit_ids = $_POST['unit_id'];

    foreach ($ingredient_ids as $index => $ing_id) {
        $ing_id = intval($ing_id);
        $qty = floatval($quantities[$index]);
        $unit_id = intval($unit_ids[$index]);

        $stmt = $conn->prepare("INSERT INTO recipes (product_id, ingredient_id, quantity_required, unit_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidi", $product_id, $ing_id, $qty, $unit_id);
        $stmt->execute();
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
    .page-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
    }
    .form-card {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .form-card h2 {
        text-align: center;
        font-size: 22px;
        margin-bottom: 25px;
        font-weight: bold;
        color: #333;
    }
    .form-group {
        margin-bottom: 18px;
    }
    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #333;
    }
    .form-group select, 
    .form-group input {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.3s;
    }
    .form-group select:focus, 
    .form-group input:focus {
        border-color: #ff4500;
    }
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    .ingredient-row {
        display: flex;
        gap: 15px;
        margin-bottom: 10px;
        align-items: center;
    }
    .ingredient-row select,
    .ingredient-row input {
        flex: 1;
    }
    .btn-remove {
        background: #dc3545;
        color: #fff;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-remove:hover {
        background: #a71d2a;
    }
    .btn-add {
        background: #28a745;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }
    .btn-add:hover {
        background: #218838;
    }
    .btn-submit {
        width: 100%;
        background: #ff4500;
        color: #fff;
        font-size: 18px;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .btn-submit:hover {
        background: #e03e00;
    }
    .select2-container {
        width: 100% !important;
    }
    .header-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    .header-logo img {
        height: 60px;
        margin-right: 10px;
    }
    .header-logo h1 {
        font-size: 32px;
        font-weight: bold;
        color: #ff4500;
        margin: 0;
    }
    .back-link {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #007bff;
        font-weight: bold;
        text-decoration: none;
    }
    .back-link:hover {
        text-decoration: none;
        color: #0056b3;
    }
</style>

<div class="header-logo">
    <img src="../../assets/images/logo.png" alt="Hot Slice Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="page-container">
    <div class="form-card">
        <h2>Add New Recipe</h2>
        <form method="POST">
            <div class="form-group" style="width:50%;">
                <label>Product</label>
                <select name="product_id" class="searchable" required>
                    <option value="">-- Select Product --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
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
                <div class="ingredient-row">
                    <select name="ingredient_id[]" class="searchable" required>
                        <option value="">-- Select Ingredient --</option>
                        <?php foreach ($ingredients as $i): ?>
                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" step="0.01" name="quantity_required[]" placeholder="Quantity" required>
                    <select name="unit_id[]" class="searchable" required>
                        <option value="">-- Select Unit --</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['base_unit']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-remove" onclick="removeRow(this)">✖</button>
                </div>
            </div>

            <div style="margin-top:10px;">
                <button type="button" class="btn-add" onclick="addRow()">+ Add Ingredient</button>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-submit">Save Recipe</button>
            </div>

            <!-- Back Link -->
            <a href="index.php" class="back-link">← Back to Recipes</a>
        </form>
    </div>
</div>

<!-- jQuery + Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.searchable').select2({
        placeholder: "-- Select --",
        allowClear: true
    });
});

function addRow() {
    let newRow = document.createElement('div');
    newRow.classList.add('ingredient-row');
    newRow.innerHTML = `
        <select name="ingredient_id[]" class="searchable" required>
            <option value="">-- Select Ingredient --</option>
            <?php foreach ($ingredients as $i): ?>
                <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" step="0.01" name="quantity_required[]" placeholder="Quantity" required>
        <select name="unit_id[]" class="searchable" required>
            <option value="">-- Select Unit --</option>
            <?php foreach ($units as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['base_unit']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn-remove" onclick="removeRow(this)">✖</button>
    `;
    document.querySelector('#ingredient-container').appendChild(newRow);
    $(newRow).find('.searchable').select2({
        placeholder: "-- Select --",
        allowClear: true
    });
}

function removeRow(button) {
    let rows = document.querySelectorAll('.ingredient-row');
    if (rows.length > 1) {
        button.closest('.ingredient-row').remove();
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
