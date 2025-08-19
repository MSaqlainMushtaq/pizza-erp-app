<?php include('../../includes/header.php'); ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: #f7f8fa;
        margin: 0;
        padding: 0;
    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
    }

    .page-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: calc(100vh - 80px); /* Adjust height to avoid navbar */
        margin-top: 80px; /* Space for navbar */
    }

    .login-container {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 350px;
        text-align: center;
    }

    .login-header {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .login-header h2 {
        font-size: 24px;
        margin: 0;
        font-weight: bold;
        color: #ff4500;
    }

    .brand-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }

    .error-message {
        color: red;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .login-form .form-group {
        margin-bottom: 15px;
        text-align: left;
        position: relative;
    }

    .login-form label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        color: #555;
    }

    .login-form input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        outline: none;
        transition: border 0.3s;
    }

    .login-form input:focus {
        border-color: #ff4500;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 68%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 16px;
        color: #555;
    }

    .error-text {
        color: red;
        font-size: 12px;
        display: none;
    }

    .login-btn {
        width: 100%;
        background: #ff4500;
        color: #fff;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .login-btn:hover {
        background: #e03e00;
    }
</style>

<div class="page-container">
    <div class="login-container">
        <div class="login-header">
            <h2>Login - Hot Slice Pizza</h2>
            <img src="../../assets/images/logo.png" alt="Logo" class="brand-logo">
        </div>

        <?php if (isset($_GET['error'])): ?>
            <p class="error-message"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form id="loginForm" action="../../controllers/auth.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter Username" required>
                <span class="error-text" id="usernameError"></span>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter Password" required>
                <span class="toggle-password" onclick="togglePassword()">👁</span>
                <span class="error-text" id="passwordError"></span>
            </div>

            <button type="submit" name="login" class="login-btn">Login</button>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        passwordField.type = passwordField.type === "password" ? "text" : "password";
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById("loginForm");
        if (form) {
            const username = document.getElementById("username");
            const password = document.getElementById("password");
            const usernameError = document.getElementById("usernameError");
            const passwordError = document.getElementById("passwordError");

            form.addEventListener("submit", function(e) {
                let isValid = true;

                usernameError.style.display = "none";
                passwordError.style.display = "none";

                if (username.value.trim() === "") {
                    usernameError.textContent = "Username is required!";
                    usernameError.style.display = "block";
                    isValid = false;
                }

                if (password.value.trim() === "") {
                    passwordError.textContent = "Password is required!";
                    passwordError.style.display = "block";
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>

<?php include('../../includes/footer.php'); ?>
