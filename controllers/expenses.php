<?php
include __DIR__ . '/../config/db.php';

// Add Expense
if (isset($_POST['add_expense'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $expense_date = $_POST['expense_date'];

    $stmt = $conn->prepare("INSERT INTO expenses (title, amount, expense_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $title, $amount, $expense_date);
    $stmt->execute();

    header("Location: /pizza-erp-app/views/expenses/index.php");
    exit;
}

// Edit Expense
if (isset($_POST['edit_expense'])) {
    $id = (int)$_POST['id'];
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $expense_date = $_POST['expense_date'];

    $stmt = $conn->prepare("UPDATE expenses SET title = ?, amount = ?, expense_date = ? WHERE id = ?");
    $stmt->bind_param("sdsi", $title, $amount, $expense_date, $id);
    $stmt->execute();

    header("Location: /pizza-erp-app/views/expenses/index.php");
    exit;
}

// Delete Expense
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: /pizza-erp-app/views/expenses/index.php");
    exit;
}
