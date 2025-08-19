<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';
include_once '../../includes/header.php';
include_once '../../includes/navbar.php';
include_once '../../includes/sidebar.php';


// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    echo "<p style='color:red; padding-left:200px; padding-top:50px;'>Access denied. Only admins can access this page.</p>";
    include_once '../../includes/footer.php';
    exit;
}
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
        <h2>Add New User</h2>
        <form id="addUserForm" action="../../controllers/users.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Enter Username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter Password" required>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="cashier">Cashier</option>
                </select>
            </div>

            <button type="submit" name="add_user" class="btn-submit">Save User</button>
        </form>
        <a href="index.php" class="back-link">← Back to Users</a>
    </div>
</div>

<script>
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;

    if (!regex.test(password)) {
        e.preventDefault();
        alert('Password must be at least 8 characters long and include at least one number and one special character.');
    }
});
</script>

<?php include_once '../../includes/footer.php'; ?>
