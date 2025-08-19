<!DOCTYPE html>
<html>
<head>
    <title>Customer Signup</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Optional -->
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 50px; }
        form { max-width: 400px; margin: auto; background: #fff; padding: 20px; box-shadow: 0 0 10px #ccc; border-radius: 8px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #28a745; color: #fff; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Customer Signup</h2>

<?php if (isset($_GET['error'])): ?>
    <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>

<form method="POST" action="../../controllers/customer_auth.php">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <textarea name="address" placeholder="Your Address" required></textarea>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="signup">Signup</button>
    <p>Already have an account? <a href="/pizza-erp-app/views/customers/login.php">Login</a></p>
</form>

</body>
</html>
