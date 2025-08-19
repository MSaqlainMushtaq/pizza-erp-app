<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: /pizza-erp-app/views/auth/login.php?error=Please+login");
    exit;
}

if (isset($_POST['create_invoice'])) {
    $sale_id = intval($_POST['sale_id']);
    $created_by = intval($_SESSION['user']['id']);
    $issued_at = date('Y-m-d H:i:s');
    $today = date('Ymd');

    $conn->begin_transaction();
    try {
        $res = $conn->query("SELECT COUNT(*) AS count FROM invoices WHERE DATE(issued_at) = CURDATE() FOR UPDATE");
        $row = $res->fetch_assoc();
        $count = $row['count'] + 1;

        $invoice_number = "INV-$today-" . str_pad($count, 4, '0', STR_PAD_LEFT);

        $check = $conn->prepare("SELECT id FROM invoices WHERE sale_id = ?");
        $check->bind_param("i", $sale_id);
        $check->execute();
        $exists = $check->get_result();

        if ($exists->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO invoices (sale_id, invoice_number, issued_at, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $sale_id, $invoice_number, $issued_at, $created_by);
            if (!$stmt->execute()) throw new Exception("Error creating invoice: " . $stmt->error);
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die($e->getMessage());
    }

    header("Location: ../views/invoices/print.php?sale_id=$sale_id");
    exit;
}

if (isset($_GET['download_pdf'])) {
    $sale_id = intval($_GET['download_pdf']);
    require_once '../libs/tcpdf/tcpdf.php';

    $stmt = $conn->prepare("
        SELECT inv.invoice_number, inv.issued_at,
               s.total_price, s.order_type, s.delivery_address, s.phone_number,
               u.username AS created_by_username
        FROM invoices inv
        JOIN sales s ON s.id = inv.sale_id
        LEFT JOIN users u ON inv.created_by = u.id
        WHERE inv.sale_id = ?
    ");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();
    if (!$invoice) die("Invoice not found.");

    $stmtDeals = $conn->prepare("
        SELECT d.name, sd.quantity, sd.unit_price 
        FROM sale_deals sd
        JOIN deals d ON sd.deal_id = d.id
        WHERE sd.sale_id = ?
    ");
    $stmtDeals->bind_param("i", $sale_id);
    $stmtDeals->execute();
    $deals = $stmtDeals->get_result();

    $stmtProds = $conn->prepare("
        SELECT p.name, si.quantity, si.unit_price
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        WHERE si.sale_id = ?
    ");
    $stmtProds->bind_param("i", $sale_id);
    $stmtProds->execute();
    $products = $stmtProds->get_result();

    function formatPhone($phone) {
        $digits = preg_replace('/\D/', '', $phone);
        return (strlen($digits) === 11) ? substr($digits, 0, 4) . '-' . substr($digits, 4) : $phone;
    }

    $pdf = new TCPDF('P', 'mm', array(80, 200), true, 'UTF-8', false);
    $pdf->SetMargins(2, 2, 2);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);

    $html = "
    <table width='100%' cellspacing='0' cellpadding='0'>
        <tr>
            <td width='20%'><img src='../assets/images/logo.jpg' height='28'></td>
            <td width='80%' style='text-align:left;'>
                <div style='font-size:12px; font-weight:bold;'>Hot Slice Pizza</div>
                <div style='font-size:8px;'>Address: Near Motorway Interchange Pasrur Road, Wadala Sandhua, Opposite to Total PARCO Petrol Pump</div>
                <div style='font-size:8px;'>Phone: +92-300-4391045 | +92-346-4391045</div>
            </td>
        </tr>
    </table>
    <hr style='border-top:1px dashed #000; margin:2px 0;'>
    ";

    $html .= "
    <div style='font-size:9px;'>
        <b>Invoice No:</b> {$invoice['invoice_number']}<br>
        <b>Date:</b> " . date('M d, Y H:i', strtotime($invoice['issued_at'])) . "<br>
        <b>Order Type:</b> {$invoice['order_type']}<br>";
    if (strtolower($invoice['order_type']) === 'delivery') {
        $html .= "<b>Phone:</b> " . formatPhone($invoice['phone_number']) . "<br>
                  <b>Address:</b> {$invoice['delivery_address']}<br>";
    }
    $html .= "<b>Created By:</b> {$invoice['created_by_username']}
    </div>
    <hr style='border-top:1px dashed #000; margin:2px 0;'>
    <table cellpadding='2' cellspacing='0' width='100%' style='font-size:9px;'>
        <tr style='border-top:1px solid #000; border-bottom:1px solid #000;'>
            <th align='left'><b>Item</b></th>
            <th align='center'><b>Qty</b></th>
            <th align='center'><b>Rate</b></th>
            <th align='right'><b>Total</b></th>
        </tr>
    ";

    $grandTotal = 0;
    while ($row = $deals->fetch_assoc()) {
        $total = $row['quantity'] * $row['unit_price'];
        $grandTotal += $total;
        $html .= "<tr>
            <td>{$row['name']} (Deal)</td>
            <td align='center'>{$row['quantity']}</td>
            <td align='center'>" . number_format($row['unit_price'], 2) . "</td>
            <td align='right'>" . number_format($total, 2) . "</td>
        </tr>";
    }
    while ($row = $products->fetch_assoc()) {
        $total = $row['quantity'] * $row['unit_price'];
        $grandTotal += $total;
        $html .= "<tr>
            <td>{$row['name']}</td>
            <td align='center'>{$row['quantity']}</td>
            <td align='center'>" . number_format($row['unit_price'], 2) . "</td>
            <td align='right'>" . number_format($total, 2) . "</td>
        </tr>";
    }

    $html .= "</table>
        <hr style='border-top:1px dashed #000; margin:1px 0;'>
        <div style='text-align:right; font-size:10px;'><b>Total: Rs. " . number_format($grandTotal, 2) . "</b></div>
        <hr style='border-top:1px dashed #000; margin:1px 0;'>
        <div style='text-align:center; font-size:8px;'>Thank you for choosing Hot Slice Pizza!</div>
    ";

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output("Invoice_{$invoice['invoice_number']}.pdf", 'D');
    exit;
}
?>
