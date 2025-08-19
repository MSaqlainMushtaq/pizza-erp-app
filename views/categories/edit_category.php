<?php
include __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../controllers/categories.php';

// ✅ Allow only admin and manager to access
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager'])) {
    echo "<p style='color:red; padding-left:20px; padding-top:50px;'>Access denied. Only admins and managers can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}

$id = intval($_GET['id']);
$cat = getCategoryById($conn, $id);

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    updateCategory($conn, $id, $name);
    header("Location: index.php");
    exit;
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #f7f8fa;
        margin: 0;
        padding: 0;
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

    .form-container {
        width: 400px;
        margin: 30px auto;
        background: #fff;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .form-container h2 {
        margin-bottom: 20px;
        color: #333;
    }

    .form-group {
        margin-bottom: 15px;
        text-align: left;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 6px;
        color: #555;
    }

    .form-group input[type="text"] {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 16px;
        outline: none;
        transition: border 0.3s;
    }

    .form-group input[type="text"]:focus {
        border-color: #ff4500;
    }

    .btn-submit {
        width: 100%;
        background: #ff4500;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s;
    }

    .btn-submit:hover {
        background: #e03e00;
    }

    .back-link {
        display: block;
        margin-top: 15px;
        text-decoration: none;
        color: #007bff;
        font-weight: bold;
        transition: color 0.3s;
    }

    .back-link:hover {
        color: #0056b3;
    }
</style>

<!-- Top Logo + Title -->
<div class="top-header">
    <img src="../../assets/images/logo.png" alt="Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="form-container">
    <h2>Edit Category</h2>
    <form method="POST">
        <div class="form-group">
            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter Category Name" value="<?= htmlspecialchars($cat['name']) ?>" required>
        </div>
        <button type="submit" name="submit" class="btn-submit">Update Category</button>
    </form>
    <a href="index.php" class="back-link">← Back to Categories</a>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
