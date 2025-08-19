<?php
require_once __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../includes/header.php';

// Fetch products
$products = mysqli_query($conn, "SELECT id, name, price FROM products");

// Fetch only active deals
$deals = mysqli_query($conn, "SELECT id, name, price FROM deals WHERE status = 'active'");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_type = $_POST['order_type'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    // ✅ Always set delivery fields correctly
    if ($order_type === 'Delivery') {
        $delivery_address = trim($_POST['delivery_address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
    } else {
        $delivery_address = null; // force null if not delivery
        $phone = null;
    }

    $items = [];
    if (isset($_POST['item_id']) && is_array($_POST['item_id'])) {
        foreach ($_POST['item_id'] as $i => $id) {
            $type = $_POST['item_type'][$i] ?? '';
            $qty = $_POST['quantity'][$i] ?? 0;
            $price = $_POST['unit_price'][$i] ?? 0;

            if ($id && $qty > 0 && $price >= 0) {
                $items[] = [
                    'product_id' => $type === 'product' ? $id : null,
                    'deal_id' => $type === 'deal' ? $id : null,
                    'quantity' => $qty,
                    'unit_price' => $price
                ];
            }
        }
    }

    if (count($items)) {
        require_once __DIR__ . '/../../controllers/sales.php';
        $created_by = $_SESSION['user']['id'] ?? 0;
        $result = addSale($conn, $order_type, $delivery_address, $payment_method, $phone, $items, $created_by);

        if ($result['success']) {
            header("Location: ../invoices/print.php?sale_id=" . $result['sale_id']);
            exit;
        } else {
            $errors[] = "Failed to add sale: " . $result['error'];
        }
    } else {
        $errors[] = "Please add at least one product or deal.";
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f7f8fa; margin: 0; padding: 0; }
.page-container { max-width: 950px; margin: 40px auto; padding: 20px; }
.logo-header { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
.logo-header img { height: 60px; margin-right: 10px; }
.logo-header h1 { font-size: 32px; color: #ff4500; font-weight: bold; margin: 0; }
.form-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.form-card h2 { text-align: center; font-size: 22px; margin-bottom: 25px; font-weight: bold; color: #333; }
.form-row { display: flex; flex-wrap: wrap; gap: 20px; }
.form-group { margin-bottom: 18px; margin-right: 15px; flex: 1; min-width: 200px; }
.form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
.form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; }
.table-container { overflow-x: auto; margin-top: 20px; }
#items_table { width: 100%; border-collapse: collapse; }
#items_table th, #items_table td { padding: 10px; vertical-align: middle; }
#items_table th { background: #f2f2f2; text-align: left; }
.item-select { width: 100%; }
.btn-add, .btn-remove { padding: 8px 14px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
.btn-add { background: #28a745; color: #fff; margin-top: 10px; }
.btn-add:hover { background: #218838; }
.btn-remove { background: #dc3545; color: #fff; }
.btn-remove:hover { background: #a71d2a; }
.btn-submit { width: 100%; background: #ff4500; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; margin-top: 15px; }
.btn-submit:hover { background: #e03e00; }
.select2-container { width: 100% !important; }
.back-link { display: block; text-align: center; margin-top: 18px; text-decoration: none; color: #007bff; font-size: 16px; font-weight: bold; }
.back-link:hover { color: #0056b3; }
</style>

<div class="page-container">
    <div class="logo-header">
        <img src="../../assets/images/logo.png" alt="Logo">
        <h1>Hot Slice Pizza</h1>
    </div>

    <div class="form-card">
        <h2>Add New Sale</h2>

        <?php foreach ($errors as $e): ?>
            <p style="color:red;"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Order Type:</label>
                    <select name="order_type" id="order_type" required>
                        <option value="Dine-in">Dine-in</option>
                        <option value="Delivery">Delivery</option>
                        <option value="Takeaway">Takeaway</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Method:</label>
                    <select name="payment_method" required>
                        <option value="Cash">Cash</option>
                        <option value="Bank Account">Bank Account</option>
                        <option value="JazzCash">JazzCash</option>
                        <option value="Easypaisa">Easypaisa</option>
                    </select>
                </div>
            </div>

            <div class="form-row" id="delivery_fields" style="display:none;">
                <div class="form-group">
                    <label>Delivery Address:</label>
                    <input type="text" name="delivery_address">
                </div>
                <div class="form-group">
                    <label>Phone Number:</label>
                    <!-- ✅ No permanent pattern -->
                    <input type="text" id="phone_field" name="phone" placeholder="03XXXXXXXXX">
                </div>
            </div>

            <h4 style="margin-top:20px;">Sale Items</h4>
            <div class="table-container">
                <table id="items_table">
                    <thead>
                        <tr>
                            <th style="width:50%;">Product / Deal</th>
                            <th style="width:15%;">Qty</th>
                            <th style="width:20%;">Unit Price</th>
                            <th style="width:15%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="item_id[]" class="item-select" required>
                                    <option value="">-- Select --</option>
                                    <optgroup label="Products">
                                        <?php mysqli_data_seek($products, 0); while ($p = mysqli_fetch_assoc($products)): ?>
                                            <option value="<?= $p['id'] ?>" data-type="product"><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endwhile; ?>
                                    </optgroup>
                                    <optgroup label="Deals">
                                        <?php mysqli_data_seek($deals, 0); while ($d = mysqli_fetch_assoc($deals)): ?>
                                            <option value="<?= $d['id'] ?>" data-type="deal"><?= htmlspecialchars($d['name']) ?></option>
                                        <?php endwhile; ?>
                                    </optgroup>
                                </select>
                                <input type="hidden" name="item_type[]" />
                            </td>
                            <td><input type="number" name="quantity[]" value="1" min="1" required></td>
                            <td>
                                <input type="number" step="0.01" class="price-input" readonly>
                                <input type="hidden" name="unit_price[]" class="price-hidden">
                            </td>
                            <td><button type="button" class="btn-remove" onclick="removeRow(this)">✖</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn-add" id="add_row_btn">+ Add Product/Deal</button>

            <button type="submit" class="btn-submit">Save Sale</button>
        </form>

        <a href="index.php" class="back-link">← Back to Sales</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function toggleFields() {
    const type = $("#order_type").val();
    const phone = $("#phone_field");

    if (type === "Delivery") {
        $("#delivery_fields").show();
        // ✅ Add validation only when Delivery
        phone.attr("pattern", "^03\\d{9}$");
        phone.attr("required", true);
    } else {
        $("#delivery_fields").hide();
        // ✅ Remove validation so it won’t block submission
        phone.removeAttr("pattern");
        phone.removeAttr("required");
        phone.val('');
        $("input[name='delivery_address']").val('');
    }
}

$("#order_type").on("change", toggleFields);

function removeRow(btn) {
    const totalRows = $("#items_table tbody tr").length;
    if (totalRows > 1) {
        $(btn).closest('tr').remove();
    } else {
        alert("You must have at least one sale item.");
    }
}

$(document).on('change', '.item-select', function () {
    const option = $(this).find(':selected');
    const id = $(this).val();
    const type = option.data('type');
    const row = $(this).closest('tr');

    row.find('input[name="item_type[]"]').val(type);

    if (id && type) {
        fetch(`../../controllers/get_price.php?id=${id}&type=${type}`)
            .then(res => res.json())
            .then(data => {
                const price = parseFloat(data.price).toFixed(2);
                row.find('.price-input').val(price);
                row.find('.price-hidden').val(price);
            });
    } else {
        row.find('.price-input').val('');
        row.find('.price-hidden').val('');
    }
});

$("#add_row_btn").on("click", function () {
    const firstRow = $("#items_table tbody tr:first");

    // Destroy select2 before cloning
    firstRow.find('.item-select').select2('destroy');

    const newRow = firstRow.clone(false);

    // Reinitialize the first row select2
    firstRow.find('.item-select').select2({ placeholder: "-- Select --", allowClear: true });

    // Reset cloned row values
    newRow.find('.item-select').val('').trigger('change');
    newRow.find('input[name="quantity[]"]').val(1);
    newRow.find('.price-input').val('');
    newRow.find('.price-hidden').val('');
    newRow.find('input[name="item_type[]"]').val('');

    $("#items_table tbody").append(newRow);

    // Initialize select2 for the new row
    newRow.find('.item-select').select2({ placeholder: "-- Select --", allowClear: true });
});

$(document).ready(function () {
    $('.item-select').select2({ placeholder: "-- Select --", allowClear: true });
    toggleFields();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
