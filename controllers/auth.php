<?php
session_start();
require_once '../config/db.php';

// ✅ Handle login
if (isset($_POST['login'])) {
    // Sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: /pizza-erp-app/views/auth/login.php?error=Please+enter+username+and+password");
        exit;
    }

    // ✅ Case-insensitive username match
    $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Check hashed password
        if (password_verify($password, $user['password'])) {
            // ✅ Store all required info in session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];

            // ✅ Regenerate session ID for security
            session_regenerate_id(true);

            header("Location: /pizza-erp-app/views/sales/index.php");
            exit;
        } else {
            header("Location: /pizza-erp-app/views/auth/login.php?error=Invalid+username+or+password");
            exit;
        }
    } else {
        header("Location: /pizza-erp-app/views/auth/login.php?error=Invalid+username+or+password");
        exit;
    }
}

// ✅ Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: /pizza-erp-app/views/auth/login.php?logout=success");
    exit;
}
?>
