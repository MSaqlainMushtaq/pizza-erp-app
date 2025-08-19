<?php
require_once __DIR__ . '/../config/db.php';

// Monthly summary
function getMonthlySummary($conn, $month, $year) {
    $summary = [];

    // Sales
    $stmt = $conn->prepare("SELECT SUM(total_price) AS total_sales FROM sales WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $summary['sales'] = $stmt->get_result()->fetch_assoc()['total_sales'] ?? 0;

    // Ingredient cost (from stock added)
    $stmt = $conn->prepare("
        SELECT SUM(quantity_added * ing.cost_per_unit) AS ingredient_cost
        FROM ingredient_stock_log isl
        JOIN ingredients ing ON isl.ingredient_id = ing.id
        WHERE MONTH(added_at) = ? AND YEAR(added_at) = ?
    ");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $summary['ingredient_cost'] = $stmt->get_result()->fetch_assoc()['ingredient_cost'] ?? 0;

    // Salaries
    $salary = $conn->query("SELECT SUM(salary) AS total_salaries FROM employees")->fetch_assoc()['total_salaries'] ?? 0;
    $summary['salaries'] = $salary;

    // Manual expenses
    $stmt = $conn->prepare("SELECT SUM(amount) AS manual_expenses FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $summary['manual_expenses'] = $stmt->get_result()->fetch_assoc()['manual_expenses'] ?? 0;

    // Total expenses
    $summary['total_expenses'] = $summary['ingredient_cost'] + $summary['salaries'] + $summary['manual_expenses'];
    $summary['profit'] = $summary['sales'] - $summary['total_expenses'];

    return $summary;
}

// Sales & Expenses for Line Chart
function getSalesExpensesLineChartData($conn, $year) {
    $salesData = [];
    $expenseData = [];

    for ($m = 1; $m <= 12; $m++) {
        // Sales
        $stmt = $conn->prepare("SELECT SUM(total_price) AS total FROM sales WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
        $stmt->bind_param("ii", $m, $year);
        $stmt->execute();
        $salesData[] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Ingredient cost (from stock added)
        $stmt2 = $conn->prepare("
            SELECT SUM(quantity_added * ing.cost_per_unit) AS ingredient_cost
            FROM ingredient_stock_log isl
            JOIN ingredients ing ON isl.ingredient_id = ing.id
            WHERE MONTH(added_at) = ? AND YEAR(added_at) = ?
        ");
        $stmt2->bind_param("ii", $m, $year);
        $stmt2->execute();
        $ingredientCost = $stmt2->get_result()->fetch_assoc()['ingredient_cost'] ?? 0;

        // Salaries
        $salary = $conn->query("SELECT SUM(salary) AS total_salaries FROM employees")->fetch_assoc()['total_salaries'] ?? 0;

        // Manual expenses
        $stmt3 = $conn->prepare("SELECT SUM(amount) AS manual_expenses FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
        $stmt3->bind_param("ii", $m, $year);
        $stmt3->execute();
        $manualExpenses = $stmt3->get_result()->fetch_assoc()['manual_expenses'] ?? 0;

        $expenseData[] = $ingredientCost + $salary + $manualExpenses;
    }

    return ['sales' => $salesData, 'expenses' => $expenseData];
}

// Product and deal bar chart
function getProductDealBarData($conn, $month, $year) {
    $products = [];
    $deals = [];

    $stmt = $conn->prepare("
        SELECT p.name, SUM(si.quantity) AS qty
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        JOIN sales s ON s.id = si.sale_id
        WHERE MONTH(s.created_at) = ? AND YEAR(s.created_at) = ?
        GROUP BY si.product_id
    ");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $products[] = ['label' => $row['name'], 'quantity' => $row['qty']];
    }

    $stmt = $conn->prepare("
        SELECT d.name, SUM(sd.quantity) AS qty
        FROM sale_deals sd
        JOIN deals d ON sd.deal_id = d.id
        JOIN sales s ON s.id = sd.sale_id
        WHERE MONTH(s.created_at) = ? AND YEAR(s.created_at) = ?
        GROUP BY sd.deal_id
    ");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $deals[] = ['label' => $row['name'], 'quantity' => $row['qty']];
    }

    return array_merge($products, $deals);
}

// Expense bar chart by title (selected month)
function getExpenseBarData($conn, $month, $year) {
    $data = [];
    $stmt = $conn->prepare("
        SELECT title, SUM(amount) AS total 
        FROM expenses 
        WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ? 
        GROUP BY title
    ");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = ['label' => $row['title'], 'amount' => $row['total']];
    }

    // Ingredient cost
    $stmt = $conn->prepare("
        SELECT SUM(quantity_added * ing.cost_per_unit) AS ingredient_cost
        FROM ingredient_stock_log isl
        JOIN ingredients ing ON isl.ingredient_id = ing.id
        WHERE MONTH(added_at) = ? AND YEAR(added_at) = ?
    ");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $ingredient = $stmt->get_result()->fetch_assoc()['ingredient_cost'] ?? 0;
    if ($ingredient > 0) {
        $data[] = ['label' => 'Ingredient Stock Cost', 'amount' => $ingredient];
    }

    // Salaries
    $salary = $conn->query("SELECT SUM(salary) AS salaries FROM employees")->fetch_assoc()['salaries'] ?? 0;
    if ($salary > 0) {
        $data[] = ['label' => 'Employee Salaries', 'amount' => $salary];
    }

    return $data;
}

// Low inventory (now returns an array)
function getLowInventory($conn) {
    $result = $conn->query("
        SELECT ing.name, ing.quantity, u.unit_name, ing.threshold
        FROM ingredients ing
        JOIN units u ON ing.unit_id = u.id
        WHERE ing.quantity <= ing.threshold
    ");
    $lowStock = [];
    while ($row = $result->fetch_assoc()) {
        $lowStock[] = $row;
    }
    return $lowStock;
}

// Recent sales
function getRecentSales($conn, $limit = 5) {
    return $conn->query("SELECT id, total_price, order_type, created_at FROM sales ORDER BY created_at DESC LIMIT $limit");
}
