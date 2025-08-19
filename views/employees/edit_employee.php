<?php
include('../../config/db.php');
require_once '../../includes/auth_check.php';
include('../../includes/header.php');
require_once '../../includes/sidebar.php';

// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    echo "<p style='color:red; padding-left:200px; padding-top:50px;'>Access denied. Only admins can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}


$id = $_GET['id'];
$result = $conn->query("SELECT * FROM employees WHERE id=$id");
$emp = $result->fetch_assoc();
?>
<style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f7f8fa; margin: 0; padding: 0; }
    .page-container { max-width: 600px; margin: 50px auto; padding: 20px; }
    .logo-header { display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .logo-header img { height: 50px; margin-right: 10px; }
    .logo-header h1 { font-size: 28px; color: #ff4500; margin: 0; }
    .form-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
    .form-card h2 { text-align: center; margin-bottom: 20px; color: #333; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; outline: none; }
    .form-group input:focus { border-color: #ff4500; }
    /* Remove spinners for number inputs */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
    /* Remove spinners for date fields in Chrome/Safari (keep calendar icon) */
    input[type=date]::-webkit-inner-spin-button,
    input[type=date]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .btn-submit { width: 100%; background: #ff4500; color: #fff; padding: 14px; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; transition: background 0.3s ease; cursor: pointer; }
    .btn-submit:hover { background: #e03e00; }
    .back-link { display: block; text-align: center; margin-top: 15px; color: #007bff; font-weight: bold; text-decoration: none; }
    .back-link:hover { color: #0056b3; }
</style>

<div class="page-container">
    <div class="logo-header">
        <img src="../../assets/images/logo.png" alt="Logo">
        <h1>Hot Slice Pizza</h1>
    </div>

    <div class="form-card">
        <h2>Edit Employee</h2>
        <form method="POST" action="../../controllers/employees.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($emp['id']) ?>">

            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" placeholder="Enter Name" value="<?= htmlspecialchars($emp['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" placeholder="example@gmail.com" value="<?= htmlspecialchars($emp['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number:</label>
                <input type="text" name="phone" placeholder="03xxxxxxxxx" value="<?= htmlspecialchars($emp['phone']) ?>" required>
            </div>
            <div class="form-group">
                <label>Role:</label>
                <input type="text" name="role" placeholder="Enter Role" value="<?= htmlspecialchars($emp['role']) ?>" required>
            </div>
            <div class="form-group">
                <label>Salary:</label>
                <input type="number" name="salary" placeholder="Enter Salary" value="<?= htmlspecialchars($emp['salary']) ?>" required>
            </div>
            <div class="form-group">
                <label>Join Date:</label>
                <input type="date" name="join_date" value="<?= htmlspecialchars($emp['join_date']) ?>" required>
            </div>
            <div class="form-group">
                <label>End Date:</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($emp['end_date']) ?>">
            </div>
            <button type="submit" name="update" class="btn-submit">Update Employee</button>
        </form>
        <a href="index.php" class="back-link">← Back to Employee List</a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
