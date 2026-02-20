<?php
session_start();

require_once "../db_config.php";
require_once "../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

function h($str)
{
    return htmlspecialchars($str ?? "", ENT_QUOTES, "UTF-8");
}

$stmtUser = $link_id->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
$stmtUser->execute([$_SESSION["user_id"]]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../heading.php" ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - LibFlow</title>
</head>

<body>
    <div class="profile-wrapper">
        <div class="profile-card">

            <div class="profile-header">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($user["name"], 0, 1)); ?>
                </div>
                <h2><?php echo h($user["name"]); ?></h2>
                <span class="badge">
                    <?php echo ($user["role"] === "admin") ? "Administrator" : "Library Member"; ?>
                </span>
            </div>

            <form class="profile-form">
                <div class="form-section">
                    <h3>Personal Information</h3>

                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" value="<?php echo h($user["name"]); ?>" disabled>
                    </div>

                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo h($user["email"]); ?>" disabled>
                    </div>

                    <div class="input-group">
                        <label>Role</label>
                        <input type="text" value="<?php echo h(ucfirst($user["role"])); ?>" disabled>
                    </div>

                    <small style="display:block; margin-top:10px; text-align: center;">
                        Profile changes can only be made by the administrator.
                    </small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-save" onclick="window.location.href='../dashboard.php'">
                        Back to Dashboard
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>

</html>