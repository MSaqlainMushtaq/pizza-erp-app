<?php
session_start();
if (!isset($_SESSION['customer'])) {
    header("Location: /views/customers/login.php");
    exit;
}

$customer = $_SESSION['customer'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($customer['name']) ?>!</h2>
    <p>Email: <?= htmlspecialchars($customer['email']) ?></p>
    <p>Phone: <?= htmlspecialchars($customer['phone']) ?? 'N/A' ?></p>
    <p>Address: <?= htmlspecialchars($customer['address']) ?? 'N/A' ?></p>

    <a href="/pizza-erp-app/views/customers/logout.php" style="color: red;">Logout</a>
</body>
</html>
