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


$result = $conn->query("SELECT * FROM employees");
?>
<style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #bfc3ccc8; margin: 0; padding-top: 40px; padding-left:160px; }
    .top-header { text-align: center; margin-top: 20px; margin-bottom: 10px; }
    .top-header img { height: 50px; vertical-align: middle; }
    .top-header h1 { display: inline-block; margin-left: 10px; font-size: 32px; color: #ff4500; vertical-align: middle; }
    .container { width: 90%; margin: 30px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
    .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .header-row h2 { margin: 0; color: #333; }
    .add-btn { padding: 10px 18px; background: #28a745; color: white; font-size: 16px; border-radius: 6px; text-decoration: none; font-weight: bold; }
    .add-btn:hover { background: #218838; }
    table { width: 100%; border-collapse: collapse; font-size: 16px; }
    th, td { padding: 12px; text-align: center; vertical-align: middle; }
    th { background: #ff4500; color: #fff; }
    tr:nth-child(even) { background: #f9f9f9; }
    .action-container { display: flex; justify-content: center; align-items: center; gap: 8px; }
    .action-btn { padding: 8px 14px; border-radius: 5px; font-size: 14px; text-decoration: none; color: #fff; display: inline-block; }
    .edit-btn {  background: #ffc107; color: #000; }
    .edit-btn:hover { background: #e0a800; }
    .delete-btn { background: #dc3545; }
    .delete-btn:hover { background: #b02a37; }
</style>

<div class="top-header">
    <img src="../../assets/images/logo.png" alt="Logo">
    <h1>Hot Slice Pizza</h1>
</div>

<div class="container">
    <div class="header-row">
        <h2>Employees List</h2>
        <a href="add_employee.php" class="add-btn">+ Add New Employee</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
       <!-- <th>Email</th> -->
            <th>Phone</th>
            <th>Role</th>
            <th>Salary (Rs.)</th>
            <th>Join Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
      <!--  <td><?= htmlspecialchars($row['email']) ?></td> -->
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= number_format($row['salary'], 2) ?></td>
            <td><?= htmlspecialchars($row['join_date']) ?></td>
            <td><?= !empty($row['end_date']) ? htmlspecialchars($row['end_date']) : "Continue" ?></td>
            <td>
                <div class="action-container">
                    <a href="edit_employee.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">Edit</a>
                    <a href="../../controllers/employees.php?delete=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Delete this employee?')">Delete</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php include('../../includes/footer.php'); ?>
