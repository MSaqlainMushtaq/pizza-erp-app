<style>
.navbar {
  background-color: #d32f2f;
  padding: 10px 20px;
  color: white;
  font-size: 18px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  display: flex;
  justify-content: space-between; /* left links + right logout */
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  box-sizing: border-box;
  margin-bottom: 30px;
}

.navbar .nav-links,
.navbar .nav-right {
  list-style-type: none;
  display: flex;
  gap: 20px;
  margin: 0;
  padding: 0;
}

.navbar .nav-links li,
.navbar .nav-right li { display: inline; }

.navbar a {
  color: white;
  text-decoration: none;
  padding: 8px 12px;
  transition: background 0.3s;
}
.navbar a:hover {
  background-color: #b71c1c;
  border-radius: 4px;
}

/* 🔹 Fix: give logout button some breathing room */
.navbar .nav-right {
  margin-right: 20px; /* moves logout link slightly left */
}
</style>

<nav class="navbar">
  <ul class="nav-links">
    <li><a href="/pizza-erp-app/views/dashboard.php">Dashboard</a></li>
    <li><a href="/pizza-erp-app/views/inventory/index.php">Inventory</a></li>
    <li><a href="/pizza-erp-app/views/products/index.php">Products</a></li>
    <li><a href="/pizza-erp-app/views/deals/index.php">Deals</a></li>
    <li><a href="/pizza-erp-app/views/sales/index.php">Sales</a></li>
    <li><a href="/pizza-erp-app/views/categories/index.php">Categories</a></li>
    <li><a href="/pizza-erp-app/views/units/index.php">Units</a></li>


  </ul>
  <ul class="nav-right">
    <li><a href="/pizza-erp-app/views/auth/logout.php">Logout</a></li>
  </ul>
</nav>
