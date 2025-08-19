<?php
include __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../controllers/products.php';
include __DIR__ . '/../../includes/header.php';

// ✅ Allow only admin and manager to access
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager'])) {
    echo "<p style='color:red; padding-left:20px; padding-top:50px;'>Access denied. Only admins and managers can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    if (!empty($name) && $category_id > 0 && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO products (name, category_id, price) VALUES (?, ?, ?)");
        $stmt->bind_param("sid", $name, $category_id, $price);
        $stmt->execute();
        $stmt->close();

        // Redirect directly to product list after successful insert
        header("Location: index.php");
        exit;
    }
}

// Fetch categories for dropdown
$cat_result = $conn->query("SELECT * FROM categories");
?>

<style>
    .page-container {
        max-width: 450px;
        margin: 40px auto;
        padding: 20px;
    }

    .logo-header {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .logo-header img {
        height: 60px;
        margin-right: 10px;
    }

    .logo-header h1 {
        font-size: 32px;
        color: #ff4500;
        font-weight: bold;
        margin: 0;
    }

    .form-card {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .form-card h2 {
        text-align: center;
        font-size: 22px;
        margin-bottom: 25px;
        font-weight: bold;
        color: #333;
    }

    .form-group {
        margin-bottom: 18px;
        text-align: left;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #333;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #ff4500;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .btn-submit {
        width: 100%;
        background: #ff4500;
        color: #fff;
        font-size: 18px;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn-submit:hover {
        background: #e03e00;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 18px;
        text-decoration: none;
        color: #007bff;
        font-size: 16px;
        font-weight: bold;
    }

    .back-link:hover {
        color: #0056b3;
    }
</style>

<div class="page-container">
    <div class="logo-header">
        <img src="../../assets/images/logo.png" alt="Hot Slice Logo">
        <h1>Hot Slice Pizza</h1>
    </div>

    <div class="form-card">
        <h2>Add New Product</h2>

        <form method="POST">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" name="name" id="name" placeholder="Enter Product Name" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($cat = $cat_result->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Price (Rs.):</label>
                <input type="number" name="price" id="price" step="0.01" placeholder="Enter Product Price" required>
            </div>

            <button class="btn-submit" type="submit">Save Product</button>
        </form>

        <a href="index.php" class="back-link">← Back to Product List</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
