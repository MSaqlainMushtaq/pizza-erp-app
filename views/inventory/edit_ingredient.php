<?php
include_once '../../config/db.php';
require_once '../../includes/auth_check.php';
include_once '../../includes/header.php';

// ✅ Allow only admin and manager to access
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager'])) {
    echo "<p style='color:red; padding-left:20px; padding-top:50px;'>Access denied. Only admins and managers can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}

if (!isset($_GET['id'])) {
    echo "<p>Invalid ingredient ID.</p>";
    include_once '../../includes/footer.php';
    exit();
}

$id = $_GET['id'];
$query = "SELECT * FROM ingredients WHERE id = $id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<p>Ingredient not found.</p>";
    include_once '../../includes/footer.php';
    exit();
}

$ingredient = mysqli_fetch_assoc($result);
$units = mysqli_query($conn, "SELECT * FROM units");

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $restock_quantity = isset($_POST['restock_quantity']) ? floatval($_POST['restock_quantity']) : 0;
    $unit_id = $_POST['unit_id'];
    $cost_per_unit = floatval($_POST['cost_per_unit']);
    $threshold = floatval($_POST['threshold']);

    // Add restock quantity to current quantity (preserve decimals)
    $new_quantity = floatval($ingredient['quantity']) + $restock_quantity;

    // Update ingredient
    $stmt = $conn->prepare("UPDATE ingredients SET name=?, quantity=?, unit_id=?, cost_per_unit=?, threshold=? WHERE id=?");
    $stmt->bind_param("sdiddi", $name, $new_quantity, $unit_id, $cost_per_unit, $threshold, $id);

    if ($stmt->execute()) {
        // Log restock (if quantity added)
        if ($restock_quantity > 0) {
            $log_stmt = $conn->prepare("INSERT INTO ingredient_stock_log (ingredient_id, quantity_added, cost_per_unit) VALUES (?, ?, ?)");
            $log_stmt->bind_param("idd", $id, $restock_quantity, $cost_per_unit);
            $log_stmt->execute();
        }

        header("Location: index.php");
        exit();
    } else {
        echo "<p>Error updating ingredient: " . $stmt->error . "</p>";
    }
}
?>

<!-- HTML FORM + STYLING -->
<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #f7f8fa;
        margin: 0;
        padding: 0;
    }

    .page-container {
        max-width: 500px;
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

    .form-group input, .form-group select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.3s;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }

    .form-group input:focus, .form-group select:focus {
        border-color: #ff4500;
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

    input[readonly] {
        background-color: #eee;
    }
</style>

<div class="page-container">
    <div class="logo-header">
        <img src="../../assets/images/logo.png" alt="Hot Slice Logo">
        <h1>Hot Slice Pizza</h1>
    </div>

    <div class="form-card">
        <h2>Edit Ingredient</h2>
        <form method="POST">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Enter Ingredient Title" value="<?= htmlspecialchars($ingredient['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Current Quantity</label>
                <input type="number" value="<?= $ingredient['quantity'] ?>" readonly step="0.01">
            </div>

            <div class="form-group">
                <label>Restock Quantity (Add more)</label>
                <input type="number" name="restock_quantity" step="0.01" placeholder="e.g. 25.00">
            </div>

            <div class="form-group">
                <label>Unit</label>
                <select name="unit_id" required>
                    <option value="">-- Select Unit --</option>
                    <?php while ($u = mysqli_fetch_assoc($units)): ?>
                        <option value="<?= $u['id'] ?>" <?= ($ingredient['unit_id'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['unit_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Cost Per Unit</label>
                <input type="number" name="cost_per_unit" step="0.01" placeholder="Enter Price" value="<?= $ingredient['cost_per_unit'] ?>" required>
            </div>

            <div class="form-group">
                <label>Threshold</label>
                <input type="number" name="threshold" step="0.01" placeholder="Enter Threshold Value" value="<?= $ingredient['threshold'] ?>" required>
            </div>

            <button type="submit" class="btn-submit">Update Ingredient</button>
        </form>
        <a href="index.php" class="back-link">← Back to Inventory</a>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
