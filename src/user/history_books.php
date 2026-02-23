<?php
session_start();

require_once "../db_config.php";
require_once "../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "user") {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$statusFilter = "";
$params = [":user_id" => $user_id];

if (isset($_GET['filter'])) {
    if ($_GET['filter'] === "returned") {
        $statusFilter = " AND t.status = 'returned' ";
    } elseif ($_GET['filter'] === "borrowed") {
        $statusFilter = " AND t.status = 'borrowed' ";
    }
}

$sql = "
SELECT 
    t.id,
    t.issue_date,
    t.return_date,
    t.status,
    b.title,
    b.author
FROM transactions t
JOIN books b ON t.book_id = b.id
WHERE t.user_id = :user_id
$statusFilter
ORDER BY t.created_at DESC
";

$stmt = $link_id->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $link_id->prepare("
    SELECT COUNT(*) AS total 
    FROM transactions 
    WHERE user_id = :user_id 
    AND status = 'returned'
");
$countStmt->execute([":user_id" => $user_id]);
$totalRead = intval($countStmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book History - LibFlow</title>
    <?php include "../heading.php"; ?>
</head>

<body>
    <div class="history-wrapper">
        <header class="history-header">
            <div class="title-section">
                <h1>Reading Journey</h1>
                <p>Track all the books you've explored so far.</p>
            </div>
            <div class="history-stats">
                <div class="stat-card">
                    <span class="stat-num"><?php echo $totalRead; ?></span>
                    <span class="stat-label">Total Read</span>
                </div>
            </div>
        </header>

        <div class="history-container">
            <div class="filter-bar">
                <a href="?filter=all"><button class="filter-btn">All Books</button></a>
                <a href="?filter=returned"><button class="filter-btn">Returned</button></a>
                <a href="?filter=borrowed"><button class="filter-btn">To Return</button></a>

                <!-- <div class="wrapper-actions">
                    <a href="../dashboard.php" class="btn-back">
                        <span class="arrow"></span> Back to Dashboard
                    </a>
                </div> -->
            </div>

            <div class="history-list">
                <?php foreach ($transactions as $row): ?>
                    <div class="history-card">
                        <div class="book-cover-mini">📖</div>
                        <div class="book-details">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="author"><?php echo htmlspecialchars($row['author']); ?></p>
                            <div class="timeline-info">
                                <span>
                                    <strong>Borrowed:</strong>
                                    <?php echo date("M d, Y", strtotime($row['issue_date'])); ?>
                                </span>

                                <?php if ($row['status'] === 'returned' && $row['return_date'] !== null): ?>
                                    <span>
                                        <strong>Returned:</strong>
                                        <?php echo date("M d, Y", strtotime($row['return_date'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="pending">
                                        <strong>Status:</strong> Still Borrowed
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="status-zone">
                            <span class="status-badge <?php echo $row['status']; ?>">
                                <?php echo $row['status'] === 'returned' ? "Completed" : "Still Reading"; ?>
                            </span>
                            <p class="tr-id">#TR-<?php echo $row['id']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>