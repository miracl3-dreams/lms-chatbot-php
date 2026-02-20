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

function h($str)
{
    return htmlspecialchars($str ?? "", ENT_QUOTES, "UTF-8");
}

function formatDate($date)
{
    if ($date == "" || $date === null)
        return "-";
    return date("M d, Y", strtotime($date));
}

function getStatusBadge($status)
{
    $status = strtolower(trim($status));

    if ($status === "returned") {
        return '<span class="badge status-returned">Returned</span>';
    }

    if ($status === "overdue") {
        return '<span class="badge status-overdue">Overdue</span>';
    }

    return '<span class="badge status-borrowed">Borrowed</span>';
}

$search = trim($_GET["search"] ?? "");

if (isset($_GET["export"]) && $_GET["export"] === "csv") {

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=transaction_logs.csv");

    $output = fopen("php://output", "w");

    fputcsv($output, ["Transaction ID", "Borrower", "Book Title", "Issue Date", "Return Date", "Status"]);

    $sql = "
        SELECT 
            t.id,
            t.issue_date,
            t.return_date,
            t.status,
            u.name AS borrower_name,
            b.title AS book_title
        FROM transactions t
        INNER JOIN users u ON u.id = t.user_id
        INNER JOIN books b ON b.id = t.book_id
        WHERE 1
    ";

    $params = [];

    if ($search !== "") {
        $sql .= " AND (
            u.name LIKE ? OR
            b.title LIKE ? OR
            t.status LIKE ? OR
            t.id LIKE ?
        )";
        $like = "%" . $search . "%";
        $params = [$like, $like, $like, $like];
    }

    $sql .= " ORDER BY t.id DESC";

    $stmt = $link_id->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {

        $finalStatus = $r["status"];
        if ($finalStatus === "borrowed" && $r["return_date"] !== null && $r["return_date"] !== "") {
            if (date("Y-m-d") > $r["return_date"]) {
                $finalStatus = "overdue";
            }
        }

        fputcsv($output, [
            "TR-" . $r["id"],
            $r["borrower_name"],
            $r["book_title"],
            $r["issue_date"],
            $r["return_date"],
            strtoupper($finalStatus)
        ]);
    }

    fclose($output);
    exit;
}

$sql = "
    SELECT 
        t.id,
        t.issue_date,
        t.return_date,
        t.status,
        u.name AS borrower_name,
        b.title AS book_title
    FROM transactions t
    INNER JOIN users u ON u.id = t.user_id
    INNER JOIN books b ON b.id = t.book_id
    WHERE 1
";

$params = [];

if ($search !== "") {
    $sql .= " AND (
        u.name LIKE ? OR
        b.title LIKE ? OR
        t.status LIKE ? OR
        t.id LIKE ?
    )";
    $like = "%" . $search . "%";
    $params = [$like, $like, $like, $like];
}

$sql .= " ORDER BY t.id DESC";

$stmt = $link_id->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = date("Y-m-d");

foreach ($transactions as &$t) {

    $finalStatus = strtolower($t["status"]);

    if ($finalStatus === "borrowed" && $t["return_date"] !== null && $t["return_date"] !== "") {
        if ($today > $t["return_date"]) {
            $finalStatus = "overdue";
        }
    }

    $t["final_status"] = $finalStatus;
}
unset($t);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Logs - Admin</title>
    <link rel="stylesheet" href="view_transactions.css">
</head>

<?php include "../heading.php"; ?>

<body>
    <div class="admin-container">
        <header class="admin-header">
            <h2>Transaction History</h2>

            <div class="header-actions">

                <form method="GET" action="view_transactions.php" style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="search" value="<?php echo h($search); ?>"
                        placeholder="Search transaction..." class="search-bar">

                    <button type="submit" class="btn-export">Search</button>

                    <a class="btn-export"
                        href="view_transactions.php?export=csv&search=<?php echo urlencode($search); ?>"
                        style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                        Export to CSV
                    </a>
                </form>

            </div>
        </header>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Borrower</th>
                        <th>Book Title</th>
                        <th>Date Borrowed</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($transactions) == 0) { ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:20px;">
                                No transactions found.
                            </td>
                        </tr>
                    <?php } else { ?>

                        <?php foreach ($transactions as $row) { ?>
                            <tr>
                                <td>#TR-<?php echo h($row["id"]); ?></td>
                                <td><?php echo h($row["borrower_name"]); ?></td>
                                <td><?php echo h($row["book_title"]); ?></td>
                                <td><?php echo h(formatDate($row["issue_date"])); ?></td>
                                <td><?php echo h(formatDate($row["return_date"])); ?></td>
                                <td><?php echo getStatusBadge($row["final_status"]); ?></td>

                                <td>
                                    <a href="view_transaction_details.php?id=<?php echo h($row["id"]); ?>" class="btn-view"
                                        style="text-decoration:none;">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } ?>

                </tbody>
            </table>
        </div>
    </div>
</body>

</html>