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


// Selected month/year
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$selectedYear  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

// Ingredient Stock Cost (virtual, from logs with cost_per_unit)
$stockSql = "
    SELECT 
        COALESCE(SUM(isl.quantity_added * COALESCE(isl.cost_per_unit, 0)), 0) AS total_stock_cost,
        MAX(isl.added_at) AS last_update
    FROM ingredient_stock_log isl
    WHERE MONTH(isl.added_at) = ? AND YEAR(isl.added_at) = ?
";
$stockStmt = $conn->prepare($stockSql);
$stockStmt->bind_param("ii", $selectedMonth, $selectedYear);
$stockStmt->execute();
$stockRow = $stockStmt->get_result()->fetch_assoc();
$ingredientStockCost = (float)($stockRow['total_stock_cost'] ?? 0);
$stockLastUpdate     = $stockRow['last_update'] ?? null;

// Manual expenses
$expStmt = $conn->prepare("
    SELECT id, title, amount, expense_date
    FROM expenses
    WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?
    ORDER BY expense_date DESC
");
$expStmt->bind_param("ii", $selectedMonth, $selectedYear);
$expStmt->execute();
$expenses = $expStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Salaries
$salaryResult   = $conn->query("SELECT COALESCE(SUM(salary), 0) AS total_salaries FROM employees");
$totalSalaries  = (float)($salaryResult->fetch_assoc()['total_salaries'] ?? 0);

// Total
$totalExpense = $ingredientStockCost + $totalSalaries;
foreach ($expenses as $e) {
    $totalExpense += (float)$e['amount'];
}

// Month dropdown
$monthOptions = '';
for ($m = 1; $m <= 12; $m++) {
    $selected = ($m === $selectedMonth) ? 'selected' : '';
    $monthOptions .= "<option value='$m' $selected>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
}
?>
<style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #bfc3ccc8; margin: 0; padding-top: 40px; padding-left: 160px; }
    .top-header { text-align: center; margin-top: 20px; margin-bottom: 10px; }
    .top-header img { height: 50px; vertical-align: middle; }
    .top-header h1 { display: inline-block; margin-left: 10px; font-size: 32px; color: #ff4500; vertical-align: middle; }

    .container { max-width: 1000px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    h2 { margin-top: 0; color: #333; }

    .filter-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
    .filter-controls { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    select, button, a.btn { padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; text-decoration: none; }
    button { background: #ff4500; color: #fff; border: none; cursor: pointer; }
    button:hover { background: #e03e00; }
    a.btn-primary { background: #28a745; color: #fff; font-size: 16px; border: none; font-weight: bold;}
    a.btn-primary:hover { background: #218838; }

    table { width: 100%; border-collapse: collapse; font-size: 16px; }
    thead th { background: #ff4500; color: #fff; text-align: left; padding: 10px; }
    tbody td { border-bottom: 1px solid #eee; padding: 10px; vertical-align: middle; }
    tr:nth-child(even) { background: #f9f9f9; }

    .actions { display: flex; gap: 8px; }
    .btn-sm { padding: 6px 10px; border-radius: 5px; font-size: 13px; text-decoration: none; color: #fff; display: inline-block; }
    .btn-warning {background: #ffc107; color: #000; }
    .btn-warning:hover { background: #e0a800; }
    .btn-danger { background: #dc3545; }
    .btn-danger:hover { background: #b02a37; }

    .meta { color: #666; font-size: 12px; }
</style>

<div class="top-header">
    <img src="../../assets/images/logo.png" alt="Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="container">
    <h2>Monthly Expenses - <?= htmlspecialchars(date("F", mktime(0, 0, 0, $selectedMonth, 1))) ?> <?= htmlspecialchars($selectedYear) ?></h2>

    <div class="filter-row">
        <form method="get" class="filter-controls">
            <select name="month"><?= $monthOptions ?></select>
            <select name="year">
                <?php for ($y = (int)date('Y'); $y >= 2023; $y--): ?>
                    <option value="<?= $y ?>" <?= ($y === $selectedYear) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">Filter</button>
        </form>
        <a href="add_expense.php" class="btn btn-primary">+ Add Manual Expense</a>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:45%;">Title</th>
                <th style="width:15%;">Amount (Rs)</th>
                <th style="width:20%;">Date</th>
                <th style="width:20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Virtual Ingredient Stock Cost -->
            <tr>
                <td><strong>Ingredient Stock Cost</strong><div class="meta">Auto-generated from stock additions</div></td>
                <td><?= number_format($ingredientStockCost, 2) ?></td>
                <td><?= $stockLastUpdate ? htmlspecialchars(date('Y-m-d H:i', strtotime($stockLastUpdate))) : '—' ?></td>
                <td><em>Auto-generated</em></td>
            </tr>

            <!-- Virtual Salary -->
            <tr>
                <td><strong>Employee Salaries</strong><div class="meta">Auto-generated monthly total</div></td>
                <td><?= number_format($totalSalaries, 2) ?></td>
                <td><?= htmlspecialchars(sprintf('%04d-%02d-01', $selectedYear, $selectedMonth)) ?></td>
                <td><em>Auto-generated</em></td>
            </tr>

            <!-- Manual Expenses -->
            <?php foreach ($expenses as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= number_format((float)$e['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($e['expense_date']) ?></td>
                    <td class="actions">
                        <a href="edit_expense.php?id=<?= (int)$e['id'] ?>" class="btn-sm btn-warning">Edit</a>
                        <a href="../../controllers/expenses.php?delete=<?= (int)$e['id'] ?>" onclick="return confirm('Delete this expense?')" class="btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h4>Total Expenses: Rs. <?= number_format($totalExpense, 2) ?></h4>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
