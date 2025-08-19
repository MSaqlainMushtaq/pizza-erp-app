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

// Fetch all users
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #bfc3ccc8;
        margin: 0;
        padding-top: 50px;
        padding-left: 160px;
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

    .container {
        width: 90%;
        margin: 20px auto;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .header-bar h2 {
        margin: 0;
        color: #333;
    }

    .btn-add {
        background: #28a745;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    .btn-add:hover {
        background: #218838;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        text-align: left;
    }

    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }

    th {
        background: #ff4500;
        color: #fff;
        text-transform: uppercase;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
        text-decoration: none;
        color: #fff;
        margin-right: 8px;
        display: inline-block;
    }

    .btn-edit {
        background: #ffc107;
        color: #000;
    }

    .btn-edit:hover {
        background: #e0a800;
    }

    .btn-delete {
        background: #dc3545;
    }

    .btn-delete:hover {
        background: #a71d2a;
    }
</style>

<!-- Top Logo + Title -->
<div class="top-header">
    <img src="../../assets/images/logo.png" alt="Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="container">
    <div class="header-bar">
        <h2>Users List</h2>
        <a href="add_user.php" class="btn-add">+ Add New User</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                <a href="../../controllers/users.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include_once '../../includes/footer.php'; ?>
