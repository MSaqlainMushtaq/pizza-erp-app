<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/tcpdf/tcpdf.php';

if (!isset($_SESSION['user'])) {
    header("Location: /pizza-erp-app/views/auth/login.php?error=Please+login");
    exit;
}

$userName = $_SESSION['user']['username'] ?? 'Unknown';

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$type = $_GET['type'] ?? '';
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';
$download_pdf = isset($_GET['download_pdf']);

if (!in_array($type, ['Sales', 'Expenses', 'Stock_added', 'Stock_usage'])) {
    die("Invalid report type.");
}
if (!validateDate($start) || !validateDate($end)) {
    die("Invalid date range.");
}

$data = [];
$totalAmount = 0;
$totalQuantity = 0;
$groupedData = [];
$totalCash = 0;
$totalOnline = 0;

// ---------------------- FETCH DATA ----------------------
switch ($type) {
    case 'Stock_usage':
        $sql = "SELECT iul.id, ing.id AS ingredient_id, ing.name AS ingredient_name, 
                       iul.quantity_used, iul.cost, iul.used_at, iul.sale_id, u.base_unit, u.unit_name
                FROM ingredient_usage_logs iul
                JOIN ingredients ing ON iul.ingredient_id = ing.id
                JOIN units u ON ing.unit_id = u.id
                WHERE DATE(iul.used_at) BETWEEN ? AND ?
                ORDER BY iul.used_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $totalQuantity += $row['quantity_used'];
            $totalAmount += $row['cost'];
            $data[] = $row;

            $id = $row['ingredient_id'];
            if (!isset($groupedData[$id])) {
                $groupedData[$id] = [
                    'name' => $row['ingredient_name'],
                    'unit_name' => $row['unit_name'],
                    'used_qty' => 0,
                    'total_cost' => 0
                ];
            }
            $groupedData[$id]['used_qty'] += $row['quantity_used'];
            $groupedData[$id]['total_cost'] += $row['cost'];
        }

        // Fetch Stock Added for Remaining
        $stockSql = "SELECT ingredient_id, SUM(quantity_added) AS total_added
                     FROM ingredient_stock_log
                     WHERE DATE(added_at) BETWEEN ? AND ?
                     GROUP BY ingredient_id";
        $stockStmt = $conn->prepare($stockSql);
        $stockStmt->bind_param("ss", $start, $end);
        $stockStmt->execute();
        $stockRes = $stockStmt->get_result();
        $stockAdded = [];
        while ($s = $stockRes->fetch_assoc()) {
            $stockAdded[$s['ingredient_id']] = $s['total_added'];
        }

        foreach ($groupedData as $id => &$g) {
            $added = $stockAdded[$id] ?? 0;
            $g['remaining_qty'] = $added - $g['used_qty'];
        }
        break;

    case 'Stock_added':
        $sql = "SELECT isl.id, i.id AS ingredient_id, i.name AS ingredient_name, isl.quantity_added, 
                        i.cost_per_unit, isl.added_at, u.base_unit, u.unit_name
                FROM ingredient_stock_log isl
                JOIN ingredients i ON isl.ingredient_id = i.id
                JOIN units u ON i.unit_id = u.id
                WHERE DATE(isl.added_at) BETWEEN ? AND ?
                ORDER BY isl.added_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $row['total_cost'] = $row['quantity_added'] * $row['cost_per_unit'];
            $totalQuantity += $row['quantity_added'];
            $totalAmount += $row['total_cost'];
            $data[] = $row;

            $id = $row['ingredient_id'];
            if (!isset($groupedData[$id])) {
                $groupedData[$id] = [
                    'name' => $row['ingredient_name'],
                    'unit_name' => $row['unit_name'],
                    'total_qty' => 0,
                    'total_cost' => 0
                ];
            }
            $groupedData[$id]['total_qty'] += $row['quantity_added'];
            $groupedData[$id]['total_cost'] += $row['total_cost'];
        }

        // Usage for Remaining
        $usageSql = "SELECT ingredient_id, COALESCE(SUM(quantity_used),0) AS used_qty
                        FROM ingredient_usage_logs
                        GROUP BY ingredient_id";
        $usageRes = $conn->query($usageSql);
        $usageData = [];
        while ($u = $usageRes->fetch_assoc()) {
            $usageData[$u['ingredient_id']] = $u['used_qty'];
        }

        foreach ($groupedData as $id => &$ing) {
            $used = $usageData[$id] ?? 0;
            $ing['remaining_qty'] = $ing['total_qty'] - $used;
        }
        break;

    case 'Sales':
        $sql = "SELECT id, total_price, payment_method, order_type, created_at AS sale_date
                FROM sales
                WHERE DATE(created_at) BETWEEN ? AND ?
                ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($data as $row) {
            if (strtolower(trim($row['payment_method'])) === 'cash') {
                $totalCash += $row['total_price'];
            } else {
                $totalOnline += $row['total_price'];
            }
            $totalAmount += $row['total_price'];
        }
        break;

    case 'Expenses':
        $sql = "SELECT id, title, amount, expense_date
                FROM expenses
                WHERE DATE(expense_date) BETWEEN ? AND ?
                ORDER BY expense_date ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $manualExpenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stockSql = "SELECT COALESCE(SUM(isl.quantity_added * COALESCE(i.cost_per_unit, 0)), 0) AS total_stock_cost
                     FROM ingredient_stock_log isl
                     INNER JOIN ingredients i ON i.id = isl.ingredient_id
                     WHERE DATE(isl.added_at) BETWEEN ? AND ?";
        $stockStmt = $conn->prepare($stockSql);
        $stockStmt->bind_param("ss", $start, $end);
        $stockStmt->execute();
        $stockCost = (float)($stockStmt->get_result()->fetch_assoc()['total_stock_cost'] ?? 0);

        $salarySql = "SELECT COALESCE(SUM(salary), 0) AS total_salaries FROM employees";
        $salaryRes = $conn->query($salarySql);
        $salaryCost = (float)($salaryRes->fetch_assoc()['total_salaries'] ?? 0);

        $data[] = ['id' => '-', 'expense_date' => $start, 'title' => 'Ingredient Stock Cost', 'amount' => $stockCost];
        $data[] = ['id' => '-', 'expense_date' => $start, 'title' => 'Employee Salaries', 'amount' => $salaryCost];
        $data = array_merge($data, $manualExpenses);

        foreach ($data as $row) {
            $totalAmount += $row['amount'];
        }
        break;
}

// ---------------------- PDF EXPORT ----------------------
if ($download_pdf) {
    $pdf = new TCPDF();
    $pdf->SetTitle("$type Report");
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $logoPath = __DIR__ . '/../assets/images/logo.jpg';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 10, 20, '', 'JPG');
    }
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 15, "Hot Slice Pizza", 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, "$type Report", 0, 1, 'C');
    $pdf->Cell(0, 8, "Date Range: $start to $end", 0, 1, 'L');
    $pdf->Cell(0, 8, "Generated by: $userName", 0, 1, 'L');
    $pdf->Ln(5);

    $html = "";

    switch ($type) {
        case 'Sales':
            $html .= '<h4>Total Sales: Rs ' . number_format($totalAmount, 2) . '</h4>
                      <h4>Cash Sales: Rs ' . number_format($totalCash, 2) . '</h4>
                      <h4>Online Sales: Rs ' . number_format($totalOnline, 2) . '</h4><br>';
            $html .= '<table border="1" cellpadding="4">
                        <tr>
                            <th bgcolor="#ff4500" color="#fff">ID</th>
                            <th bgcolor="#ff4500" color="#fff">Date</th>
                            <th bgcolor="#ff4500" color="#fff">Order Type</th>
                            <th bgcolor="#ff4500" color="#fff">Payment Method</th>
                            <th bgcolor="#ff4500" color="#fff">Total Price (Rs.)</th>
                        </tr>';
            foreach ($data as $row) {
                $html .= "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['sale_date']}</td>
                            <td>{$row['order_type']}</td>
                            <td>{$row['payment_method']}</td>
                            <td>" . number_format($row['total_price'], 2) . "</td>
                          </tr>";
            }
            $html .= '</table>';
            break;

        case 'Expenses':
            $html .= '<h4>Total Expenses: Rs ' . number_format($totalAmount, 2) . '</h4><br>';
            $html .= '<table border="1" cellpadding="4">
                        <tr>
                            <th bgcolor="#ff4500" color="#fff">ID</th>
                            <th bgcolor="#ff4500" color="#fff">Date</th>
                            <th bgcolor="#ff4500" color="#fff">Title</th>
                            <th bgcolor="#ff4500" color="#fff">Amount (Rs.)</th>
                        </tr>';
            foreach ($data as $row) {
                $html .= "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['expense_date']}</td>
                            <td>{$row['title']}</td>
                            <td>" . number_format($row['amount'], 2) . "</td>
                          </tr>";
            }
            $html .= '</table>';
            break;

        case 'Stock_added':
            $html .= '<h4>Total Cost of All Ingredients: Rs ' . number_format($totalAmount, 2) . '</h4><br>';
            $html .= '<table border="1" cellpadding="4">
                        <tr>
                            <th bgcolor="#ff4500" color="#fff">ID</th>
                            <th bgcolor="#ff4500" color="#fff">Date</th>
                            <th bgcolor="#ff4500" color="#fff">Ingredient</th>
                            <th bgcolor="#ff4500" color="#fff">Quantity</th>
                            <th bgcolor="#ff4500" color="#fff">Base Unit</th>
                            <th bgcolor="#ff4500" color="#fff">Unit Cost (Rs.)</th>
                            <th bgcolor="#ff4500" color="#fff">Total Cost (Rs.)</th>
                        </tr>';
            foreach ($data as $row) {
                $html .= "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['added_at']}</td>
                            <td>{$row['ingredient_name']}</td>
                            <td>" . number_format($row['quantity_added'], 2) . "</td>
                            <td>{$row['base_unit']}</td>
                            <td>" . number_format($row['cost_per_unit'], 2) . "</td>
                            <td>" . number_format($row['total_cost'], 2) . "</td>
                          </tr>";
            }
            $html .= '</table><br>';

            // Ingredient Summary
            $html .= '<h4>Ingredient-wise Summary</h4><table border="1" cellpadding="4">
                        <tr>
                            <th bgcolor="#ff4500" color="#fff">Ingredient</th>
                            <th bgcolor="#ff4500" color="#fff">Total Quantity</th>
                            <th bgcolor="#ff4500" color="#fff">Remaining Quantity</th>
                            <th bgcolor="#ff4500" color="#fff">Unit</th>
                            <th bgcolor="#ff4500" color="#fff">Total Cost (Rs.)</th>
                        </tr>';
            foreach ($groupedData as $ing) {
                $html .= "<tr>
                            <td>{$ing['name']}</td>
                            <td>" . number_format($ing['total_qty'], 2) . "</td>
                            <td>" . number_format($ing['remaining_qty'], 2) . "</td>
                            <td>{$ing['unit_name']}</td>
                            <td>" . number_format($ing['total_cost'], 2) . "</td>
                          </tr>";
            }
            $html .= '</table>';
            break;

        case 'Stock_usage':
            $html .= '<h4>Total Usage Cost: Rs ' . number_format($totalAmount, 2) . '</h4><br>';
            $html .= '<table border="1" cellpadding="4">
                        <tr>
                            <th bgcolor="#ff4500" color="#fff">ID</th>
                            <th bgcolor="#ff4500" color="#fff">Date</th>
                            <th bgcolor="#ff4500" color="#fff">Ingredient</th>
                            <th bgcolor="#ff4500" color="#fff">Quantity Used</th>
                            <th bgcolor="#ff4500" color="#fff">Base Unit</th>
                            <th bgcolor="#ff4500" color="#fff">Cost (Rs.)</th>
                            <th bgcolor="#ff4500" color="#fff">Sale ID</th>
                        </tr>';
            foreach ($data as $row) {
                $html .= "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['used_at']}</td>
                            <td>{$row['ingredient_name']}</td>
                            <td>" . number_format($row['quantity_used'], 2) . "</td>
                            <td>{$row['base_unit']}</td>
                            <td>" . number_format($row['cost'], 2) . "</td>
                            <td>{$row['sale_id']}</td>
                          </tr>";
            }
            $html .= '</table><br>';

            $html .= '<h4>Ingredient-wise Summary</h4><table border="1" cellpadding="4">
                        <tr>
                            <th bgcolor="#ff4500" color="#fff">Ingredient</th>
                            <th bgcolor="#ff4500" color="#fff">Total Used Quantity</th>
                            <th bgcolor="#ff4500" color="#fff">Remaining Quantity</th>
                            <th bgcolor="#ff4500" color="#fff">Unit</th>
                            <th bgcolor="#ff4500" color="#fff">Total Cost (Rs.)</th>
                        </tr>';
            foreach ($groupedData as $ing) {
                $html .= "<tr>
                            <td>{$ing['name']}</td>
                            <td>" . number_format($ing['used_qty'], 2) . "</td>
                            <td>" . number_format($ing['remaining_qty'], 2) . "</td>
                            <td>{$ing['unit_name']}</td>
                            <td>" . number_format($ing['total_cost'], 2) . "</td>
                          </tr>";
            }
            $html .= '</table>';
            break;
    }

    $pdf->writeHTML($html);
    $pdf->Output("{$type}_Report.pdf", 'I');
    exit;
}

header("Location: /pizza-erp-app/views/reports/{$type}_report_range.php?start=$start&end=$end");
exit;
