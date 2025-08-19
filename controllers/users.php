<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

// -------------------------------------------
// ADD USER
// -------------------------------------------
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = trim($_POST['role']);

    // ✅ Validate role: must be one of allowed values
    if (empty($role) || !in_array($role, ['admin', 'manager', 'cashier'])) {
        die('Invalid role selected.');
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();

    header("Location: ../views/users/index.php");
    exit;
}

// -------------------------------------------
// EDIT USER
// -------------------------------------------
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);

    // ✅ Validate role here too
    if (empty($role) || !in_array($role, ['admin', 'manager', 'cashier'])) {
        die('Invalid role selected.');
    }

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, role=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $role, $id);
    }

    $stmt->execute();

    header("Location: ../views/users/index.php");
    exit;
}

// -------------------------------------------
// DELETE USER
// -------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: ../views/users/index.php");
    exit;
}
