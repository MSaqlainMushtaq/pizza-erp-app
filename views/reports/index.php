<?php
require_once '../../includes/auth_check.php';
include_once '../../includes/header.php';
include_once '../../includes/navbar.php';
require_once '../../includes/sidebar.php';

// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    echo "<p style='color:red; padding-left:200px; padding-top:50px;'>Access denied. Only admins can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}

?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #bfc3ccc8;
        margin: 0;
        padding-top: 50px;
        padding-left: 160px;
    }

    .top-header {
        text-align: center;
        margin-top: 20px;
        margin-bottom: 10px;
    }

    .top-header img {
        height: 50px;
        vertical-align: middle;
    }

    .top-header h1 {
        display: inline-block;
        margin-left: 10px;
        font-size: 32px;
        color: #ff4500;
        vertical-align: middle;
    }

    .container {
        width: 90%;
        margin: 20px auto;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .header-bar h2 {
        margin: 0;
        color: #333;
    }

    form .form-row {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        margin-bottom: 20px;
    }

    form .form-group {
        flex: 1;
        margin-right: 20px;
    }

    label {
        font-weight: bold;
        margin-bottom: 6px;
        display: block;
    }

    select, input[type="date"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
    }

    .btn-generate {
        background: #ff4500;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 100%;
    }

    .btn-generate:hover {
        background: #ed4001ff;
    }

    ul {
        margin-top: 15px;
        line-height: 1.7;
    }

    ul li strong {
        color: #333;
    }
</style>

<!-- Top Logo + Title -->
<div class="top-header">
    <img src="../../assets/images/logo.png" alt="Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="container">
    <div class="header-bar">
        <h2>Reports Dashboard</h2>
    </div>

    <p>Select a report type and a date range to view and export detailed records.</p>

    <form action="/pizza-erp-app/controllers/reports_range.php" method="GET">
        <div class="form-row">
            <div class="form-group">
                <label for="type">Report Type</label>
                <select name="type" id="type" required>
                    <option value="">-- Select Report Type --</option>
                    <option value="Sales">Sales Report</option>
                    <option value="Expenses">Expense Report</option>
                    <option value="Stock_added">Stock Added Report</option>
                    <option value="Stock_usage">Stock Usage Report</option>
                </select>
            </div>
            <div class="form-group">
                <label for="start">Start Date</label>
                <input type="date" name="start" id="start" required>
            </div>
            <div class="form-group">
                <label for="end">End Date</label>
                <input type="date" name="end" id="end" required>
            </div>
           
        </div>
        <div>
             <div class="form-group" style="max-width: 150px;">
                <button type="submit" class="btn-generate">Generate</button>
            </div>
        </div>
    </form>

    <hr class="my-4">

    <h4>📄 Available Reports</h4>
    <ul>
        <li><strong>Sales Report</strong> — View total sales, payment methods, and orders for the selected range.</li>
        <li><strong>Expense Report</strong> — See all expenses recorded within the selected range.</li>
        <li><strong>Stock Added Report</strong> — Track stock purchases and cost.</li>
        <li><strong>Stock Usage Report</strong> — Track ingredient usage with costs and linked sales.</li>
    </ul>
</div>

<?php include_once '../../includes/footer.php'; ?>
