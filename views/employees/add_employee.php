<?php 
include __DIR__ . '/../../config/db.php';
require_once '../../includes/auth_check.php';
include __DIR__ . '/../../controllers/employees.php';
include __DIR__ . '/../../includes/header.php';
require_once '../../includes/sidebar.php';

// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    echo "<p style='color:red; padding-left:200px; padding-top:50px;'>Access denied. Only admins can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}

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
    /* Remove spinners for date fields in Chrome/Safari (keeps calendar icon) */
    input[type=date]::-webkit-inner-spin-button,
    input[type=date]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .error-text { color: red; font-size: 12px; display: none; }
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
        <h2>Add New Employee</h2>
        <form id="employeeForm" method="POST" action="/pizza-erp-app/controllers/employees.php">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" placeholder="Enter Name"required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" placeholder="example@gmail.com"required>
            </div>
            <div class="form-group">
                <label>Phone Number:</label>
                <input type="text" name="phone" placeholder="03xxxxxxxxx"required>
            </div>
            <div class="form-group">
                <label>Role:</label>
                <input type="text" name="role" placeholder="Enter Role"required>
            </div>
            <div class="form-group">
                <label>Salary:</label>
                <input type="number" name="salary" placeholder="Enter Salary"required>
            </div>
            <div class="form-group">
                <label>Join Date:</label>
                <input type="date" name="join_date" required>
            </div>
            <button type="submit" name="add" class="btn-submit">Save Employee</button>
        </form>
        <a href="index.php" class="back-link">← Back to Employee List</a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
