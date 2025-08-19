<?php
session_start();
include('../config/db.php');

// ✅ CUSTOMER SIGNUP
if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $created_at = date("Y-m-d H:i:s");

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        header("Location: /pizza-erp-app/views/customers/signup.php?error=Email+already+exists");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO customers (name, email, password, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $created_at);
    $stmt->execute();

    header("Location: /pizza-erp-app/views/customers/login.php?success=Account+created");
    exit;
}

// ✅ CUSTOMER LOGIN
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['customer'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ];
        header("Location: /pizza-erp-app/views/customers/dashboard.php");
        exit;
    } else {
        header("Location: /pizza-erp-app/views/customers/login.php?error=Invalid+email+or+password");
        exit;
    }
}

// ✅ CUSTOMER LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /pizza-erp-app/views/customers/login.php?logout=success");
    exit;
}
?>
