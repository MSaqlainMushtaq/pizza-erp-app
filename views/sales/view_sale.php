<?php
include __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../controllers/sales.php';
include __DIR__ . '/../../includes/header.php';

$id = $_GET['id'] ?? 0;

// Fetch sale details
$sale = $conn->query("SELECT * FROM sales WHERE id = " . (int)$id)->fetch_assoc();
$saleItems = getSaleItems($conn, $id);
$saleDeals = getSaleDeals($conn, $id);

$totalAmount = 0;
foreach ($saleItems as $item) {
    $totalAmount += $item['quantity'] * $item['unit_price'];
}
foreach ($saleDeals as $deal) {
    $totalAmount += $deal['quantity'] * $deal['unit_price'];
}
?>

<style>
body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f7f8fa; margin: 0; padding: 0; }
.page-container { max-width: 950px; margin: 40px auto; padding: 20px; }
.logo-header { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
.logo-header img { height: 60px; margin-right: 10px; }
.logo-header h1 { font-size: 32px; color: #ff4500; font-weight: bold; margin: 0; }
.form-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.form-card h2 { text-align: center; font-size: 22px; margin-bottom: 25px; font-weight: bold; color: #333; }
.form-row { display: flex; flex-wrap: wrap; gap: 20px; }
.form-group { margin-bottom: 18px; margin-left:15px; flex: 1; min-width: 200px; }
.form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
.form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; background-color: #f9f9f9; }
.table-container { overflow-x: auto; margin-top: 20px; }
#items_table { width: 100%; border-collapse: collapse; }
#items_table th, #items_table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
#items_table th { background: #ff4500; color: #fff; border: none; }
#items_table tfoot td { font-weight: bold; background: #f9f9f9; }
.back-link { display: block; text-align: center; margin-top: 18px; text-decoration: none; color: #007bff; font-size: 16px; font-weight: bold; }
.back-link:hover { color: #0056b3; }
</style>

<div class="page-container">
    <!-- Logo Header -->
    <div class="logo-header">
        <img src="../../assets/images/logo.png" alt="Logo">
        <h1>Hot Slice Pizza</h1>
    </div>

    <!-- Card -->
    <div class="form-card">
        <h2>Sale Details # <?= htmlspecialchars($id) ?> </h2>

        <!-- Order Info -->
        <div class="form-row">
            <div class="form-group">
                <label>Order Type:</label>
                <input type="text" value="<?= htmlspecialchars($sale['order_type']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Payment Method:</label>
                <input type="text" value="<?= htmlspecialchars($sale['payment_method']) ?>" readonly>
            </div>
        </div>

        <?php if (strtolower($sale['order_type']) === 'delivery'): ?>
        <div class="form-row">
            <div class="form-group">
                <label>Phone Number:</label>
                <input type="text" value="<?= htmlspecialchars($sale['phone_number']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Delivery Address:</label>
                <input type="text" value="<?= htmlspecialchars($sale['delivery_address']) ?>" readonly>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Order Date:</label>
                <input type="text" value="<?= date("d M Y h:i A", strtotime($sale['created_at'])) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Total Price:</label>
                <input type="text" value="Rs. <?= number_format($totalAmount, 2) ?>" readonly>
            </div>
        </div>

        <!-- Items Table -->
        <h4 style="margin-top:20px;">Sale Items</h4>
        <div class="table-container">
            <table id="items_table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($saleItems as $item): ?>
                    <tr>
                        <td>Product</td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>Rs. <?= number_format($item['unit_price'], 2) ?></td>
                        <td>Rs. <?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php foreach ($saleDeals as $deal): ?>
                    <tr>
                        <td>Deal</td>
                        <td><?= htmlspecialchars($deal['name']) ?></td>
                        <td><?= $deal['quantity'] ?></td>
                        <td>Rs. <?= number_format($deal['unit_price'], 2) ?></td>
                        <td>Rs. <?= number_format($deal['quantity'] * $deal['unit_price'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;">Total:</td>
                        <td>Rs. <?= number_format($totalAmount, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Back Link -->
        <a href="index.php" class="back-link">← Back to Sales</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
