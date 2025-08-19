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


$products = $conn->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];

    $items = [];
    foreach ($_POST['product_id'] as $index => $pid) {
        $items[] = [
            'product_id' => $pid,
            'quantity' => $_POST['quantity'][$index]
        ];
    }

    if (addDeal($conn, $name, $price, $items)) {
        header("Location: index.php");
        exit;
    } else {
        echo "<div class='page-container'><div class='form-card'><p style='color:red;'>❌ Error adding deal.</p></div></div>";
    }
}
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #f7f8fa;
        margin: 0;
        padding: 0;
    }
    .page-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
    }
    .logo-header {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    .logo-header img {
        height: 60px;
        margin-right: 10px;
    }
    .logo-header h1 {
        font-size: 32px;
        color: #ff4500;
        font-weight: bold;
        margin: 0;
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
        margin-right: 15px;
    }
    .form-row {
        display: flex;
        gap: 20px;
    }
    .form-row .form-group {
        flex: 1;
    }
    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #333;
    }
    .form-group input, 
    .form-group select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.3s;
    }
    .form-group input:focus, 
    .form-group select:focus {
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
    .deal-item-row {
        display: flex;
        gap: 15px;
        margin-bottom: 10px;
        align-items: center;
    }
    .deal-item-row select,
    .deal-item-row input {
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
        text-decoration: none;
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
    .back-link {
        display: block;
        text-align: center;
        margin-top: 18px;
        text-decoration: none;
        color: #007bff;
        font-size: 16px;
        font-weight: bold;
    }
    .back-link:hover {
        color: #0056b3;
    }
    .select2-container {
        width: 100% !important;
        z-index: 9999;
    }
    .select2-dropdown {
        z-index: 99999;
    }
</style>

<div class="page-container">
    <div class="logo-header">
        <img src="../../assets/images/logo.png" alt="Hot Slice Logo">
        <h1>Hot Slice Pizza</h1>
    </div>

    <div class="form-card">
        <h2>Add New Deal</h2>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Deal Name</label>
                    <input type="text" name="name" placeholder="Enter Deal Name" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" placeholder="Enter Deal Price" required>
                </div>
            </div>

            <h4 style="margin-top:25px;">Deal Items</h4>
            <div id="deal-items">
                <div class="deal-item-row">
                    <select name="product_id[]" class="product-dropdown" required>
                        <option value="">-- Select Product --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity[]" placeholder="Quantity" step="0.01" required>
                    <button type="button" class="btn-remove" onclick="removeItem(this)">✖</button>
                </div>
            </div>

            <div style="margin-top:10px;">
                <button type="button" class="btn-add" onclick="addItem()">+ Add More</button>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-submit">Save Deal</button>
            </div>
        </form>
        <a href="index.php" class="back-link">← Back to Deals</a>
    </div>
</div>

<!-- jQuery + Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.product-dropdown').select2({
        placeholder: "-- Select Product --",
        allowClear: true
    });
});

function addItem() {
    // Create a fresh new row instead of cloning to avoid copying selected value
    let newRow = document.createElement('div');
    newRow.classList.add('deal-item-row');

    // Build fresh select element
    let selectHTML = `<select name="product_id[]" class="product-dropdown" required>
                        <option value="">-- Select Product --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                      </select>`;

    // Quantity input
    let qtyHTML = `<input type="number" name="quantity[]" placeholder="Quantity" step="0.01" required>`;

    // Remove button
    let removeBtn = `<button type="button" class="btn-remove" onclick="removeItem(this)">✖</button>`;

    newRow.innerHTML = selectHTML + qtyHTML + removeBtn;

    document.querySelector('#deal-items').appendChild(newRow);

    // Initialize select2 for the new dropdown
    $(newRow).find('.product-dropdown').select2({
        placeholder: "-- Select Product --",
        allowClear: true
    });
}

function removeItem(button) {
    let rows = document.querySelectorAll('.deal-item-row');
    if (rows.length > 1) {
        button.closest('.deal-item-row').remove();
    }
}
</script>

<?php include_once '../../includes/footer.php'; ?>
