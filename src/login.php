<?php
session_start();

require_once "db_config.php";
require_once "include/lx.pdodb.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email == "" || $password == "") {
        $error = "Please fill up all fields.";
    } else {

        $stmt = $link_id->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid email or password.";
        } else if (!password_verify($password, $user["password"])) {
            $error = "Invalid email or password.";
        } else {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];

            if ($user["role"] === "admin") {
                header("Location: admin/dashboard.php");
                exit;
            } else {
                header("Location: dashboard.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "heading.php" ?>
    <title>Library Login</title>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <h2 class="form-title">Welcome Back!</h2>
            <p class="form-subtitle">Please enter your details to login</p>

            <?php if ($error != "") {
                echo "<p class='error-msg'>$error</p>";
            } ?>

            <form action="login.php" method="POST" id="loginForm">
                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-group password-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="passwordField" placeholder="Password" required>
                    <span class="toggle-password" id="togglePassword">👁️</span>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Login</span>
                    <div class="loader" id="loader"></div>
                </button>
            </form>

            <p class="footer-text">
                Don't have an account? <a href="register.php">Sign Up</a>
            </p>
        </div>

        <script src="javascript/login.js"></script>

    </div>
</body>

</html>