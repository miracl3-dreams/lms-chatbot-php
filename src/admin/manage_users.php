<?php
session_start();

require_once "../db_config.php";
require_once "../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$message = "";
$error = "";

$stmtUser = $link_id->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
$stmtUser->execute([$_SESSION["user_id"]]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $new_password = $_POST["new_password"] ?? "";

    if ($name === "") {
        $error = "Name is required.";
    } else {

        $stmtUpdate = $link_id->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmtUpdate->execute([$name, $_SESSION["user_id"]]);

        if (trim($new_password) !== "") {

            if (strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {

                $hashed = password_hash($new_password, PASSWORD_DEFAULT);

                $stmtPass = $link_id->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtPass->execute([$hashed, $_SESSION["user_id"]]);
            }
        }

        if ($error === "") {

            $_SESSION["user_name"] = $name;

            $stmtUser = $link_id->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
            $stmtUser->execute([$_SESSION["user_id"]]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            $message = "Profile updated successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - LibFlow</title>
    <?php include "../heading.php" ?>
</head>

<body>
    <div class="profile-wrapper">
        <div class="profile-card">
            <!-- <div class="card-top-actions">
                <a href="../dashboard.php" class="btn-back">
                    <span class="arrow"></span> Back to Dashboard
                </a>
            </div> -->

            <div class="profile-header">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($user["name"], 0, 1)); ?>
                </div>
                <h2>
                    <?php echo htmlspecialchars($user["name"]); ?>
                </h2>
                <span class="badge">Library Admin</span>
            </div>

            <?php if ($message != ""): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error != ""): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="user_profile.php" method="POST" class="profile-form">
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user["name"]); ?>"
                        placeholder="Enter your name" required>
                    </div>

                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user["email"]); ?>"
                        disabled>
                        <small>Email cannot be changed.</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Security</h3>
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Leave blank to keep current">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel"
                        onclick="window.location.href='../dashboard.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>