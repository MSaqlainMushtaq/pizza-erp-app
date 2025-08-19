<style>
.sidebar {
  width: 150px;
  background: #6c757d;
  height: calc(100vh - 50px); /* full height minus navbar */
  position: fixed;
  top: 50px; /* directly under navbar */
  left: 0;
  padding-top: 20px;
  z-index: 999;
}
.sidebar a {
  display: block;
  padding: 12px 15px;
  color: white;
  text-decoration: none;
}
.sidebar a:hover { background: #565e64; }


</style>

<div class="sidebar">
  <a href="/pizza-erp-app/views/dashboard.php">Dashboard</a>
  <a href="/pizza-erp-app/views/recipes/index.php">Recipes</a>
  <a href="/pizza-erp-app/views/expenses/index.php">Expenses</a>
  <a href="/pizza-erp-app/views/employees/index.php">Employees</a>
  <a href="/pizza-erp-app/views/users/index.php">Users</a>
    <a href="/pizza-erp-app/views/reports/index.php">Reports</a>

</div>
