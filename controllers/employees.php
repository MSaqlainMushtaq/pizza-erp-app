<?php
include __DIR__ . '/../config/db.php';

if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];
    $role = $_POST['role'];
    $join_date = $_POST['join_date'];

    $stmt = $conn->prepare("INSERT INTO employees (name, email, phone, salary, role, join_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdss", $name, $email, $phone, $salary, $role, $join_date);
    $stmt->execute();
    header("Location: /pizza-erp-app/views/employees/index.php");
    exit;
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];
    $role = $_POST['role'];
    $join_date = $_POST['join_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;

    $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, phone=?, salary=?, role=?, join_date=?, end_date=? WHERE id=?");
    $stmt->bind_param("sssdsssi", $name, $email, $phone, $salary, $role, $join_date, $end_date, $id);
    $stmt->execute();
    header("Location: /pizza-erp-app/views/employees/index.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM employees WHERE id=$id");
    header("Location: /pizza-erp-app/views/employees/index.php");
    exit;
}
?>
