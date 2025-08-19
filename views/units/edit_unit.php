<?php
include __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../includes/header.php';

// ✅ Allow only admin and manager to access
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager'])) {
    echo "<p style='color:red; padding-left:20px; padding-top:50px;'>Access denied. Only admins and managers can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}

if (!isset($_GET['id'])) {
    echo "<p>Unit ID is missing.</p>";
    exit;
}

$id = $_GET['id'];
$unit = $conn->query("SELECT * FROM units WHERE id = $id")->fetch_assoc();

if (!$unit) {
    echo "<p>Unit not found.</p>";
    exit;
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #f7f8fa;
        margin: 0;
        padding: 0;
    }

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

    .form-group input {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.3s;
    }

    .form-group input:focus {
        border-color: #ff4500;
    }

    /* Remove arrows from number input */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield; /* For Firefox */
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
        <h2>Edit Unit</h2>
        <form method="POST" action="../../controllers/units.php">
            <input type="hidden" name="id" value="<?= $unit['id'] ?>">

            <div class="form-group">
                <label>Unit Name</label>
                <input type="text" name="unit_name" placeholder="Enter Unit Name" value="<?= htmlspecialchars($unit['unit_name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Base Unit</label>
                <input type="text" name="base_unit" placeholder="E.g. gram, ml" value="<?= htmlspecialchars($unit['base_unit']) ?>" required>
            </div>

            <div class="form-group">
                <label>Conversion Factor</label>
                <input type="number" name="conversion_factor" step="0.001" placeholder="E.g. 1000" value="<?= $unit['conversion_factor'] ?>" required>
            </div>

            <button type="submit" name="edit_unit" class="btn-submit">Update Unit</button>
        </form>
        <a href="index.php" class="back-link">← Back to Units</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
