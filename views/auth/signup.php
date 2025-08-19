<?php
require_once '../../config/db.php';

$msg = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check if user already exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "Username already exists.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, role, created_at) VALUES ('$username', '$hashed', '$role', NOW())";
        if (mysqli_query($conn, $sql)) {
            $msg = "User registered successfully. Redirecting to login...";
            $success = true;
        } else {
            $msg = "Error: " . mysqli_error($conn);
        }
    }
}
?>

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
        height: calc(100vh - 80px);
        margin-top: 80px;
    }

    .signup-container {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 350px;
        text-align: center;
    }

    .signup-header {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .signup-header h2 {
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

    .message {
        font-size: 14px;
        margin-bottom: 10px;
    }

    .message.success {
        color: green;
    }

    .message.error {
        color: red;
    }

    .signup-form .form-group {
        margin-bottom: 15px;
        text-align: left;
    }

    .signup-form label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        color: #555;
    }

    .signup-form input,
    .signup-form select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        outline: none;
        transition: border 0.3s;
    }

    .signup-form input:focus,
    .signup-form select:focus {
        border-color: #ff4500;
    }

    .password-wrapper {
        position: relative;
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

    .signup-btn {
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

    .signup-btn:hover {
        background: #e03e00;
    }

    .login-link {
        margin-top: 15px;
        display: block;
        font-size: 14px;
        color: #333;
    }

    .login-link a {
        color: #ff4500;
        font-weight: bold;
        text-decoration: none;
    }

    .login-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="page-container">
    <div class="signup-container">
        <div class="signup-header">
            <h2>Signup - Hot Slice Pizza</h2>
            <img src="../../assets/images/logo.png" alt="Logo" class="brand-logo">
        </div>

        <?php if ($msg): ?>
            <p class="message <?= $success ? 'success' : 'error' ?>"><?= $msg ?></p>
        <?php endif; ?>

        <form id="signupForm" method="POST" class="signup-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter Username" required>
                <span class="error-text" id="usernameError"></span>
            </div>

            <div class="form-group password-wrapper">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter Password" required>
                <span class="toggle-password" onclick="togglePassword()">👁</span>
                <span class="error-text" id="passwordError"></span>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="cashier">Cashier</option>
                </select>
            </div>

            <button type="submit" class="signup-btn">Signup</button>
        </form>

        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php if ($success): ?>
<script>
    setTimeout(() => {
        window.location.href = "login.php";
    }, 2000);
</script>
<?php endif; ?>

<script>
function togglePassword() {
    const passwordField = document.getElementById("password");
    passwordField.type = passwordField.type === "password" ? "text" : "password";
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById("signupForm");
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

        // Password validation: at least 8 chars, 1 special character, 1 number
        const passwordValue = password.value.trim();
        const passwordPattern = /^(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;

        if (passwordValue === "") {
            passwordError.textContent = "Password is required!";
            passwordError.style.display = "block";
            isValid = false;
        } else if (!passwordPattern.test(passwordValue)) {
            passwordError.textContent = "Password must be at least 8 chars and include 1 number & 1 special character.";
            passwordError.style.display = "block";
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

<?php include('../../includes/footer.php'); ?>
