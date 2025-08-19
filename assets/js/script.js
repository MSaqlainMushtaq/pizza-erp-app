document.addEventListener('DOMContentLoaded', () => {
    console.log("Pizza ERP loaded successfully");

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
