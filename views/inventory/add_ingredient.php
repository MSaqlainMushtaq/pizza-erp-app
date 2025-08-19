<?php
include '../../includes/header.php';
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

// ✅ Allow only admin and manager to access
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['role']), ['admin', 'manager'])) {
    echo "<p style='color:red; padding-left:20px; padding-top:50px;'>Access denied. Only admins and managers can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}


// Get units list
$units = mysqli_query($conn, "SELECT * FROM units");

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $unit_id = $_POST['unit_id'];
    $cost_per_unit = $_POST['cost_per_unit'];
    $threshold = $_POST['threshold'];

    // Insert into ingredients table
    $stmt = $conn->prepare("INSERT INTO ingredients (name, quantity, unit_id, cost_per_unit, threshold) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siddi", $name, $quantity, $unit_id, $cost_per_unit, $threshold);

    if ($stmt->execute()) {
        $ingredient_id = $stmt->insert_id;

        // Also log initial stock in ingredient_stock_log
        if ($quantity > 0) {
            $logStmt = $conn->prepare("INSERT INTO ingredient_stock_log (ingredient_id, quantity_added, cost_per_unit) VALUES (?, ?, ?)");
            $logStmt->bind_param("iid", $ingredient_id, $quantity, $cost_per_unit);
            $logStmt->execute();
            $logStmt->close();
        }

        // Redirect
        header("Location: index.php");
        exit();
    } else {
        echo "<div class='page-container'><div class='form-card'><p style='color:red;'>❌ Error adding ingredient: " . $conn->error . "</p></div></div>";
    }

    $stmt->close();
    $conn->close();
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

    .form-group input, .form-group select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.3s;
    }

    .form-group input:focus, .form-group select:focus {
        border-color: #ff4500;
    }

    /* Remove arrows from number inputs */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
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
        <h2>Add New Ingredient</h2>
        <form method="POST">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Enter Ingrident Title" required>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" step="0.01" name="quantity" placeholder="Enter Quantity" required>
            </div>

            <div class="form-group">
                <label>Unit</label>
                <select name="unit_id" required>
                    <option value="">-- Select Unit --</option>
                    <?php while ($u = mysqli_fetch_assoc($units)): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['unit_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Cost Per Unit</label>
                <input type="number" step="0.01" name="cost_per_unit" placeholder="Enter Price" required>
            </div>

            <div class="form-group">
                <label>Threshold</label>
                <input type="number" step="0.01" name="threshold" placeholder="Enter Threshold Value" required>
            </div>

            <button type="submit" class="btn-submit">Save Ingredient</button>
        </form>
        <a href="index.php" class="back-link">← Back to Inventory</a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
