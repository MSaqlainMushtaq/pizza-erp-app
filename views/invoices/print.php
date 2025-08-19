<?php
require_once __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../includes/invoice_header.php';

$sale_id = isset($_GET['sale_id']) ? intval($_GET['sale_id']) : 0;
if ($sale_id <= 0) {
    echo "<p>Invalid sale ID.</p>";
    exit;
}

$sql = "
    SELECT inv.invoice_number, inv.issued_at,
           s.total_price, s.order_type, s.delivery_address, s.phone_number,
           COALESCE(u.username, 'Unknown') AS created_by_username
    FROM invoices inv
    JOIN sales s ON s.id = inv.sale_id
    LEFT JOIN users u ON inv.created_by = u.id
    WHERE inv.sale_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
if (!$invoice) {
    echo "<p>No invoice found for this sale.</p>";
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$parts = explode('-', $invoice['invoice_number']);
if (isset($parts[2])) {
    $parts[2] = str_pad($parts[2], 4, '0', STR_PAD_LEFT);
}
$invoice_no_formatted = implode('-', $parts);

function formatPhone($phone) {
    $digits = preg_replace('/\D/', '', $phone);
    return (strlen($digits) === 11) ? substr($digits, 0, 4) . '-' . substr($digits, 4) : $phone;
}

$stmt2 = $conn->prepare("
    SELECT d.name, sd.quantity, sd.unit_price 
    FROM sale_deals sd
    JOIN deals d ON sd.deal_id = d.id
    WHERE sd.sale_id = ?
");
$stmt2->bind_param("i", $sale_id);
$stmt2->execute();
$deals = $stmt2->get_result();

$stmt3 = $conn->prepare("
    SELECT p.name, si.quantity, si.unit_price
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$stmt3->bind_param("i", $sale_id);
$stmt3->execute();
$products = $stmt3->get_result();
?>
<style>
body { font-family: Arial, sans-serif; padding: 0;}
.invoice-container { width: 280px; margin: auto; font-size: 12px; }
.header-row { display: flex; align-items: center; justify-content: flex-start; border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px; }
.header-row img { width: 45px; margin-right: 8px; }
.header-text { line-height: 1.2; }
.header-text h1 { font-size: 16px; margin: 0; }
.header-text div { font-size: 11px; }
.invoice-info p { margin: 2px 0; }
.invoice-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.invoice-table th, .invoice-table td { border-bottom: 1px dashed #000; padding: 2px; }
.invoice-table th { font-weight: bold; border-top: 1px solid #000; }
.total-amount { border-top: 1px dashed #000; padding-top: 3px; text-align: right; font-weight: bold; margin-top: 2px; }
.back-link { display: block; text-align: center; margin-top: 18px; text-decoration: none; color: #007bff; font-size: 16px; font-weight: bold; }
.back-link:hover { color: #0056b3; }
.footer-text { text-align: center; font-size: 11px; margin-top: 4px; }
.no-print { text-align: center; margin-top: 8px; }
.no-print a { display: inline-block; margin-top: 6px; }

@media print { .no-print { display: none; } }
</style>

<div class="invoice-container">
    <div class="header-row">
        <img src="../../assets/images/logo.png" alt="Logo">
        <div class="header-text">
            <h1>Hot Slice Pizza</h1>
            <div>Address: Near Motorway Interchange Pasrur Road, Wadala Sandhua, Opposite to Total PARCO Petrol Pump</div>
            <div>Phone: +92-300-4391045 | +92-346-4391045</div>
        </div>
    </div>

    <div class="invoice-info">
        <p><strong>Invoice No:</strong> <?= htmlspecialchars($invoice_no_formatted) ?></p>
        <p><strong>Date:</strong> <?= date('M d, Y H:i', strtotime($invoice['issued_at'])) ?></p>
        <p><strong>Order Type:</strong> <?= htmlspecialchars($invoice['order_type']) ?></p>
        <?php if (strtolower($invoice['order_type']) === 'delivery'): ?>
            <p><strong>Phone:</strong> <?= htmlspecialchars(formatPhone($invoice['phone_number'])) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($invoice['delivery_address']) ?></p>
        <?php endif; ?>
        <p><strong>Created By:</strong> <?= htmlspecialchars($invoice['created_by_username']) ?></p>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grandTotal = 0;
            while ($row = $deals->fetch_assoc()):
                $subtotal = $row['quantity'] * $row['unit_price'];
                $grandTotal += $subtotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?> (Deal)</td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= number_format($row['unit_price'], 2) ?></td>
                    <td><?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endwhile; ?>

            <?php while ($row = $products->fetch_assoc()):
                $subtotal = $row['quantity'] * $row['unit_price'];
                $grandTotal += $subtotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= number_format($row['unit_price'], 2) ?></td>
                    <td><?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-amount">Grand Total: Rs. <?= number_format($grandTotal, 2) ?></div>
    <div class="footer-text">Thank you for choosing Hot Slice Pizza!</div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Print Invoice</button>
        <a href="../../controllers/invoices.php?download_pdf=<?= $sale_id ?>" class="btn btn-primary">Download PDF</a>
        <br>
        <a href="../sales/index.php" class="back-link">← Back to Sales</a>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
