<?php
require_once "db_config.php";
require_once "include/lx.pdodb.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $rawPassword = $_POST["password"] ?? "";

    if ($name == "" || $email == "" || $rawPassword == "") {
        die("Please fill up all fields.");
    }

    $password = password_hash($rawPassword, PASSWORD_DEFAULT);

    $stmt = $link_id->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "This email already exists!";
        exit;
    }

    $arr_user = array();
    $arr_user["name"] = $name;
    $arr_user["email"] = $email;
    $arr_user["password"] = $password;
    $arr_user["role"] = "user";

    $result = PDO_InsertRecord($link_id, "users", $arr_user, false);

    if ($result === true) {
        echo "Registration Successfully!";
        header("Location: login.php");
        exit;
    } else {
        echo "Registration failed: " . $result;
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "heading.php" ?>
    <title>Library Registration</title>
</head>

<body>
    <div class="register">
        <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Create User Account</h2>
        <form action="register.php" method="POST">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group password-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" id="passwordField" placeholder="Password" required>
                <span class="toggle-password" id="togglePassword">👁️</span>
            </div>

            <button type="submit">Sign Up</button>
        </form>
        <p style="text-align: center; font-size: 12px; margin-top: 15px; color: #777;">
            Already have an account? <a href="login.php"
                style="color: #2ecc71; text-decoration: none; font-weight: 700;">Log in</a>
        </p>
    </div>

    <script src="javascript/register.js"></script>

</body>

</html>