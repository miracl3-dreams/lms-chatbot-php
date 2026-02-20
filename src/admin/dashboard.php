<?php
session_start();

require_once "../db_config.php";
require_once "../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

$cacheDir = __DIR__ . "/cache";
if (!is_dir($cacheDir))
    mkdir($cacheDir, 0755, true);

$cacheFile = $cacheDir . "/dashboard_stats.json";
$cacheTTL = 300;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    $stats = json_decode(file_get_contents($cacheFile), true);
    $total_books = intval($stats['total_books'] ?? 0);
    $active_users = intval($stats['active_users'] ?? 0);
    $borrowed = intval($stats['borrowed'] ?? 0);
} else {
    $stmtBooks = $link_id->prepare("SELECT IFNULL(SUM(quantity), 0) AS total_books FROM books");
    $stmtBooks->execute();
    $totalBooks = $stmtBooks->fetch(PDO::FETCH_ASSOC);
    $total_books = intval($totalBooks["total_books"] ?? 0);

    $stmtUsers = $link_id->prepare("SELECT COUNT(*) AS active_users FROM users WHERE role = 'user'");
    $stmtUsers->execute();
    $totalUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC);
    $active_users = intval($totalUsers["active_users"] ?? 0);

    $stmtBorrowed = $link_id->prepare("SELECT COUNT(*) AS borrowed_count FROM transactions WHERE status = 'borrowed'");
    $stmtBorrowed->execute();
    $totalBorrowed = $stmtBorrowed->fetch(PDO::FETCH_ASSOC);
    $borrowed = intval($totalBorrowed["borrowed_count"] ?? 0);

    file_put_contents($cacheFile, json_encode([
        'total_books' => $total_books,
        'active_users' => $active_users,
        'borrowed' => $borrowed
    ]));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LibFlow</title>
    <?php include "../heading.php" ?>
</head>

<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-area">
                    <div class="logo">Lib<span>Flow</span></div>
                    <p class="admin-label">Admin Panel</p>
                </div>
                <div class="menu-toggle" id="admin-burger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>

            <nav class="sidebar-menu" id="nav-menu">
                <a href="dashboard.php" class="active">Overview</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="add_book.php">Add New Book</a>
                <a href="view_books.php">View Book Inventory</a>
                <a href="view_transactions.php">Transactions</a>
                <div class="menu-divider"></div>
                <a href="../logout.php" class="logout">Logout</a>
            </nav>
        </aside>

        <main class="content">
            <header class="header">
                <div class="header-text">
                    <h1>Overview</h1>
                    <p>Welcome back, Admin: <span><?php echo htmlspecialchars($_SESSION["user_name"]); ?></span></p>
                </div>
            </header>

            <section class="stats-row">
                <div class="stat-item">
                    <span class="stat-label">Total Books</span>
                    <span class="stat-value"><?php echo number_format($total_books); ?></span>
                </div>

                <div class="stat-item">
                    <span class="stat-label">Active Users</span>
                    <span class="stat-value"><?php echo number_format($active_users); ?></span>
                </div>

                <div class="stat-item">
                    <span class="stat-label">Borrowed</span>
                    <span class="stat-value"><?php echo number_format($borrowed); ?></span>
                </div>
            </section>

            <section class="user-grid">
                <div class="info-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="action-buttons">
                        <a href="add_book.php" class="btn-primary">Add New Book</a>
                        <a href="view_transactions.php" class="edit-btn">Check Logs</a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="../javascript/burger-a-menu.js"></script>
    <script src="../javascript/burger-a-dashboard.js"></script>
</body>

</html>