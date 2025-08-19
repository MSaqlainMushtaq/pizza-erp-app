<?php
include __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../controllers/sales.php';
include __DIR__ . '/../../includes/header.php';


// Only one portion that can manager or admin can access
$isAuthorized = isset($_SESSION['user']) && in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager']);




$day = $_GET['day'] ?? date('d');
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

$sales = getFilteredSales($conn, $day, $month, $year);

// Totals
$cashTotal = 0;
$onlineTotal = 0;
$grandTotal = 0;

foreach ($sales as $sale) {
    $grandTotal += $sale['total_price'];
    if (strtolower(trim($sale['payment_method'])) === 'cash') {
        $cashTotal += $sale['total_price'];
    } else {
        $onlineTotal += $sale['total_price'];
    }
}
?>
<style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #bfc3ccc8; margin: 0;         padding-top: 40px; }
    .top-header { text-align: center; margin-top: 20px; margin-bottom: 10px; }
    .top-header img { height: 50px; vertical-align: middle; }
    .top-header h1 { display: inline-block; margin-left: 10px; font-size: 32px; color: #ff4500; vertical-align: middle; }

    .container { max-width: 1100px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    h2 { margin-top: 0; color: #333; }

    .filter-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
    .filter-controls { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    select, button, a.btn { padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; text-decoration: none; }
    button { background: #ff4500; color: #fff; border: none; cursor: pointer; }
    button:hover { background: #e03e00; }
    a.btn-primary { background: #28a745; color: #fff; font-size: 16px; border: none; font-weight: bold; }
    a.btn-primary:hover { background: #218838; }

    table { width: 100%; border-collapse: collapse; font-size: 16px; margin-top: 10px; }
    thead th { background: #ff4500; color: #fff; text-align: left; padding: 10px; }
    tbody td { border-bottom: 1px solid #eee; padding: 10px; vertical-align: middle; }
    tr:nth-child(even) { background: #f9f9f9; }

    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-sm { padding: 6px 10px; border-radius: 5px; font-size: 13px; text-decoration: none; color: #fff; display: inline-block; }
    .btn-warning { background: #ffc107; color: #000; }
    .btn-warning:hover { background: #e0a800; }
    .btn-danger { background: #dc3545; }
    .btn-danger:hover { background: #b02a37; }

    .totals { margin: 15px 0; display: flex; gap: 20px; flex-wrap: wrap; }
    .total-card { flex: 1; min-width: 200px; background: #f5f5f5; padding: 15px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .total-card h4 { margin: 0; font-size: 18px; color: #333; }
    .total-card p { margin: 5px 0 0; font-size: 20px; font-weight: bold; color: #ff4500; }
</style>

<!-- Header -->
<div class="top-header">
    <img src="../../assets/images/logo.png" alt="Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="container">
    <h2>Sales Records (<?= htmlspecialchars($day) ?>-<?= htmlspecialchars($month) ?>-<?= htmlspecialchars($year) ?>)</h2>

    <!-- This start the admin manager access logic -->
     <?php if ($isAuthorized): ?>
    
    <!-- Totals moved here -->
    <div class="totals">
        <div class="total-card">
            <h4>Cash Amount Total</h4>
            <p>Rs. <?= number_format($cashTotal, 2) ?></p>
        </div>
        <div class="total-card">
            <h4>Online Amount Total</h4>
            <p>Rs. <?= number_format($onlineTotal, 2) ?></p>
        </div>
        <div class="total-card">
            <h4>Overall Total Sales</h4>
            <p>Rs. <?= number_format($grandTotal, 2) ?></p>
        </div>
    </div>

  <!--   This End the admin or manager access logic -->  
        <?php else: ?>
            <p style="color:red; padding:20px;">Access denied. Only admins and managers can view sales data.</p>
        <?php endif; ?>


    <div class="filter-row">
        <form method="get" class="filter-controls">
            <label>Day:</label>
            <select name="day">
                <?php for ($d = 1; $d <= 31; $d++): ?>
                    <option value="<?= str_pad($d, 2, "0", STR_PAD_LEFT) ?>" <?= (int)$d == (int)$day ? 'selected' : '' ?>><?= $d ?></option>
                <?php endfor; ?>
            </select>

            <label>Month:</label>
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= str_pad($m, 2, "0", STR_PAD_LEFT) ?>" <?= (int)$m == (int)$month ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                <?php endfor; ?>
            </select>

            <label>Year:</label>
            <select name="year">
                <?php for ($y = 2023; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= (int)$y == (int)$year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <button type="submit">Filter</button>
        </form>



        <a href="add_sale.php" class="btn btn-primary">+ Add New Sale</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Total Price</th>
                <th>Order Type</th>
                <th>Payment Method</th>
                <th>Delivery Address</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sales) > 0): ?>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= $sale['id'] ?></td>
                        <td>Rs. <?= number_format($sale['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($sale['order_type']) ?></td>
                        <td><?= htmlspecialchars($sale['payment_method']) ?></td>
                        <td><?= $sale['delivery_address'] ?: '-' ?></td>
                        <td><?= $sale['created_at'] ?></td>
                        <td class="actions">
                            <a href="view_sale.php?id=<?= $sale['id'] ?>" class="btn-sm btn-warning">View</a>
                            <a href="/pizza-erp-app/views/sales/delete_sale.php?id=<?= $sale['id'] ?>" onclick="return confirm('Are you sure?')" class="btn-sm btn-danger">Delete</a>
                            <form method="POST" action="/pizza-erp-app/controllers/invoices.php" style="display:inline;">
                                <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
                                <button type="submit" name="create_invoice" class="btn-sm btn-primary" style="background:#28a745;">Invoice</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;">No sales found for this date.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
