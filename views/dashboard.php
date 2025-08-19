<?php
require_once '../controllers/dashboard.php';
require_once '../includes/auth_check.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    echo "<p style='color:red; padding-left:200px; padding-top:50px;'>Access denied. Only admins can access this page.</p>";
    include_once '../includes/footer.php';
    exit;
}

$month = $_GET['month'] ?? date('m');
$year  = $_GET['year']  ?? date('Y');

$summary        = getMonthlySummary($conn, $month, $year);
$lineData       = getSalesExpensesLineChartData($conn, $year);
$barData        = getProductDealBarData($conn, $month, $year);
$expenseBarData = getExpenseBarData($conn, $month, $year);
$lowStock       = getLowInventory($conn);
$recentSales    = getRecentSales($conn);
?>

<?php include_once '../includes/sidebar.php'; ?>

<style>
    body { background: #bfc3ccc8; margin:0; padding-top:30px; padding-left:160px; font-family:'Segoe UI', Tahoma, sans-serif; }

    .dashboard-container{
        max-width:1200px; margin:20px auto; background:#fff; border-radius:12px;
        box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:20px;
    }

    .logo-header{ display:flex; align-items:center; justify-content:center; gap:12px; }
    .logo-header img{ height:55px; }
    .logo-header h1{ color:#ff4500; margin:0; }
    .page-title{ margin:16px 0 8px; color:#333; font-weight:700; }

    .filter-form{ display:flex; align-items:center; gap:10px; margin:12px 0 20px; flex-wrap:wrap; }
    .filter-form label{ color:#444; font-weight:600; margin-right:6px; }
    .filter-form select, .filter-form button{
        padding:8px 10px; font-size:14px; border-radius:6px; border:1px solid #ccc; background:#fff;
    }
    .filter-form button{ background:#ff4500; color:#fff; border:none; cursor:pointer; }
    .filter-form button:hover{ background:#e03e00; }

    .row{ display:flex; gap:20px; flex-wrap:wrap; align-items:stretch; }
    .half{ flex:1; min-width:300px; }

    .card{
        background:#fff; padding:18px; border-radius:10px;
        box-shadow:0 3px 8px rgba(0,0,0,0.08);
        height:100%;
        display:flex; flex-direction:column;
    }
    .card h4{ margin:0 0 12px; color:#333; }

    .summary-card{ background:#fffaf2; border-left:5px solid #ff9800; min-height:220px; }
    
    .inventory-card{ background:#eef6ff; border-left:5px solid #2196f3; min-height:220px; }
    .inventory-content{ flex:1; overflow-y:auto; }
    .inventory-list{ margin:0; padding-left:18px; }
    .inventory-list li{ margin:6px 0; }

    .charts-row{ display:flex; gap:20px; flex-wrap:wrap; align-items:stretch; margin-top:20px; }
    .chart-card{ flex:1; min-width:300px; background:#fff; border-radius:10px; padding:15px; box-shadow:0 3px 8px rgba(0,0,0,0.08); }
    .chart-card h4{ margin-bottom:10px; }

    .chart-card canvas{ width:100% !important; display:block; border-radius:6px; }
    #lineChart{ height:260px !important; }
    #pieChart{  height:220px !important; max-width:260px; margin:0 auto; }

    .graph-desc{ font-size:13px; color:#555; margin-top:8px; }

    .full-width-chart{
        background:#fff; padding:15px; margin-top:20px; border-radius:10px; box-shadow:0 3px 8px rgba(0,0,0,0.08);
    }
    .full-width-chart-inner{
        max-width:800px; margin:0 auto;
    }
    /* Bar chart height */
    .full-width-chart canvas{ height:220px !important; }

    table{ width:100%; border-collapse:collapse; margin-top:10px; background:#fff; border-radius:8px; overflow:hidden; }
    th, td{ padding:10px; border-bottom:1px solid #eee; text-align:center; }
    th{ background:#ff4500; color:#fff; }
    tr:nth-child(even) { background: #f9f9f9; }
    .badge-out{ color:#fff; background:#e53935; padding:2px 8px; border-radius:12px; font-size:12px; }
</style>

<div class="dashboard-container">
    <div class="logo-header">
        <img src="../assets/images/logo.png" alt="Logo">
        <h1>Hot Slice Pizza</h1>
    </div>
    <h2 class="page-title">Dashboard</h2>

    <form method="get" class="filter-form">
        <label>Select Month & Year:</label>
        <select name="month">
            <?= implode('', array_map(fn($m) => "<option value='$m'".($m==$month?' selected':'').">".date('F', mktime(0,0,0,$m,1))."</option>", range(1,12))) ?>
        </select>
        <select name="year">
            <?php for($y=2023;$y<=date('Y');$y++): ?>
                <option value="<?= $y ?>" <?= ($y==$year?'selected':'') ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <div class="row" style="align-items:stretch;">
        <div class="card summary-card half">
            <h4>Summary — <?= date("F", mktime(0,0,0,$month,1)) ?> <?= $year ?></h4>
            <p><strong>Total Sales:</strong> Rs. <?= number_format($summary['sales'], 2) ?></p>
            <p><strong>Total Expenses:</strong> Rs. <?= number_format($summary['total_expenses'], 2) ?></p>
            <p><strong>Profit:</strong> Rs. <?= number_format($summary['profit'], 2) ?></p>
        </div>
        <div class="card inventory-card half">
            <h4>Low Inventory</h4>
            <div class="inventory-content">
                <?php if (count($lowStock) > 0): ?>
                    <ul class="inventory-list">
                        <?php foreach($lowStock as $r): ?>
                            <li>
                                <strong><?= htmlspecialchars($r['name']) ?></strong>
                                —
                                <?php if((float)$r['quantity'] <= 0): ?>
                                    <span class="badge-out">Out of Stock</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($r['quantity']).' '.htmlspecialchars($r['unit_name']) ?>
                                <?php endif; ?>
                                &nbsp;|&nbsp;<em>Threshold:</em> <?= htmlspecialchars($r['threshold']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>All inventory is sufficient.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h4>Sales vs Expenses vs Profit</h4>
            <canvas id="pieChart"></canvas>
            <div class="graph-desc">
                • Visual share of Sales, Expenses, and Profit for the selected month.<br>
                • Quick snapshot of performance composition.
            </div>
        </div>
        <div class="chart-card">
            <h4>Monthly Sales & Expenses</h4>
            <canvas id="lineChart"></canvas>
            <div class="graph-desc">
                • Trends across the year (<?= $year ?>) for Sales and Expenses.<br>
                • Helps spot seasonal peaks and cost drifts.
            </div>
        </div>
    </div>

    <div class="full-width-chart">
        <h4>Products & Deals Sold (<?= date("F", mktime(0,0,0,$month,1)) ?> <?= $year ?>)</h4>
        <div class="full-width-chart-inner">
            <canvas id="barChart"></canvas>
        </div>
        <div class="graph-desc">
            • Quantity sold for each product/deal in the selected month.
        </div>
    </div>

    <div class="full-width-chart">
        <h4>Monthly Expense Breakdown</h4>
        <div class="full-width-chart-inner">
            <canvas id="expenseBarChart"></canvas>
        </div>
        <div class="graph-desc">
            • Shows manual expenses plus automatic components (stock cost, salaries).
        </div>
    </div>

    <div class="full-width-chart">
        <h4>Recent Sales</h4>
        <?php if ($recentSales->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Total (Rs.)</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
                <?php while($sale = $recentSales->fetch_assoc()): ?>
                    <tr>
                        <td><?= $sale['id'] ?></td>
                        <td><?= number_format($sale['total_price'],2) ?></td>
                        <td><?= htmlspecialchars($sale['order_type']) ?></td>
                        <td><?= htmlspecialchars($sale['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No sales available.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const lineData  = <?= json_encode($lineData) ?>;
    const barData   = <?= json_encode($barData) ?>;
    const expenseBar= <?= json_encode($expenseBarData) ?>;
    const summary   = <?= json_encode($summary) ?>;
    const monthLabels = <?= json_encode(array_map(fn($m)=>date('M', mktime(0,0,0,$m,1)), range(1,12))) ?>;

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [
                { label: 'Sales', data: lineData.sales, borderColor: '#4caf50', backgroundColor: 'rgba(76,175,80,0.08)', borderWidth: 2, tension: 0.25, fill: true },
                { label: 'Expenses', data: lineData.expenses, borderColor: '#f44336', backgroundColor: 'rgba(244,67,54,0.08)', borderWidth: 2, tension: 0.25, fill: true }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true } } }
    });

    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: { labels: ['Sales', 'Expenses', 'Profit'], datasets: [{ data: [summary.sales||0, summary.total_expenses||0, summary.profit||0], backgroundColor: ['#4caf50', '#f44336', '#2196f3'] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{ position:'bottom' } } }
    });

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: { labels: barData.map(i => i.label), datasets: [{ label: 'Sold Qty', data: barData.map(i => i.quantity), backgroundColor: '#ff9800' }] },
        options:{ responsive:true, plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true } } }
    });

    new Chart(document.getElementById('expenseBarChart'), {
        type: 'bar',
        data: { labels: expenseBar.map(i => i.label), datasets: [{ label: 'Expense Amount (Rs.)', data: expenseBar.map(i => i.amount), backgroundColor: '#9c27b0' }] },
        options:{ responsive:true, plugins:{ legend:{ position:'top' }}, scales:{ y:{ beginAtZero:true } } }
    });
</script>

<?php include_once '../includes/footer.php'; ?>
