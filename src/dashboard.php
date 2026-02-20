<?php
session_start();

require_once "db_config.php";
require_once "include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin") {
    header("Location: admin/dashboard.php");
    exit;
}

function h($str)
{
    return htmlspecialchars($str ?? "", ENT_QUOTES, "UTF-8");
}

$user_id = intval($_SESSION["user_id"]);

$stmtTotal = $link_id->prepare("SELECT COUNT(*) AS total FROM transactions WHERE user_id = ?");
$stmtTotal->execute([$user_id]);
$totalBorrowed = intval($stmtTotal->fetch(PDO::FETCH_ASSOC)["total"] ?? 0);

$stmtPending = $link_id->prepare("SELECT COUNT(*) AS pending FROM transactions WHERE user_id = ? AND status = 'borrowed'");
$stmtPending->execute([$user_id]);
$pendingReturns = intval($stmtPending->fetch(PDO::FETCH_ASSOC)["pending"] ?? 0);

$stmtActive = $link_id->prepare("
    SELECT 
        t.id,
        t.issue_date,
        b.title,
        b.author
    FROM transactions t
    INNER JOIN books b ON b.id = t.book_id
    WHERE t.user_id = ?
      AND t.status = 'borrowed'
    ORDER BY t.id DESC
    LIMIT 5
");
$stmtActive->execute([$user_id]);
$activeLoans = $stmtActive->fetchAll(PDO::FETCH_ASSOC);

$firstName = explode(" ", trim($_SESSION["user_name"] ?? "User"))[0];
?>

<!DOCTYPE html>
<html lang="en">
<?php include "heading.php" ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
</head>

<body>
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <div class="sidebar-top">
                <div class="logo">Lib<span>Flow</span></div>

                <div class="menu-toggle" id="dashboard-burger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>

            <nav id="nav-menu">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="user/history_books.php">Books</a>
                <a href="user/user_profile.php">Profile</a>
                <div class="logout-container">
                    <a href="logout.php" class="logout">Logout</a>
                </div>
            </nav>
        </div>

        <main class="content">
            <header class="header">
                <div class="header-left">
                    <h1>Welcome, <?php echo h($firstName); ?>!</h1>
                    <p>It's a great day to read a book.</p>
                </div>
                <div class="search-box">
                    <input type="text" placeholder="Search for books...">
                </div>
            </header>

            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-value"><?php echo $totalBorrowed; ?></span>
                    <span class="stat-label">Books Borrowed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $pendingReturns; ?></span>
                    <span class="stat-label">Pending Returns</span>
                </div>
            </div>

            <div class="user-grid">
                <div class="status-card">
                    <h3>Current Activity</h3>

                    <?php if (count($activeLoans) == 0) { ?>
                        <div class="empty-state">
                            <div class="empty-icon">📂</div>
                            <p>No active loans at the moment.</p>
                            <a href="borrow.php" class="btn-primary">Borrow Your First Book</a>
                        </div>
                    <?php } else { ?>

                        <div style="margin-top: 10px;">
                            <?php foreach ($activeLoans as $loan) { ?>
                                <div style="padding: 12px; border-bottom: 1px solid #eee;">
                                    <div style="font-weight: 600;">
                                        <?php echo h($loan["title"]); ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: #666;">
                                        <?php echo h($loan["author"]); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #888; margin-top: 4px;">
                                        Borrowed: <?php echo date("M d, Y", strtotime($loan["issue_date"])); ?>
                                        • Transaction #TR-<?php echo h($loan["id"]); ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <div style="margin-top: 15px; text-align:center;">
                            <a href="borrow.php" class="btn-primary">Borrow Another Book</a>
                            <a href="return.php" class="btn-primary">Return a Book</a>
                        </div>

                    <?php } ?>

                </div>
            </div>
        </main>
    </div>

    <div class="chatbot-wrapper">
        <div class="chatbot-window" id="chat-window">
            <div class="chatbot-header">
                <div class="bot-info">
                    <div class="bot-avatar">🤖</div>
                    <div>
                        <h4>LibFlow Bot</h4>
                        <span>Online | Assistant</span>
                    </div>
                </div>
                <button class="close-chat" id="close-chat">&times;</button>
            </div>
            <div class="chatbot-messages" id="chat-messages">
                <div class="message bot">
                    Hello
                    <?php echo h($firstName); ?>! How can I help you today?
                </div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="user-input" placeholder="Ask a question...">
                <button id="send-btn">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <button class="chat-bubble" id="chat-bubble">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="white">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"></path>
            </svg>
        </button>
    </div>

    <script src="javascript/burger-dashboard-menu.js"></script>
    <script src="javascript/chatbot.js"></script>

</body>

</html>