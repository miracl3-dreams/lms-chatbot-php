<?php
session_start();

require_once "db_config.php";
require_once "include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $borrow_id_raw = trim($_POST["borrow_id"] ?? "");
    $condition = trim($_POST["condition"] ?? "good");

    if ($borrow_id_raw == "") {
        $error = "Please enter Borrow ID.";
    } else {

        if (!ctype_digit($borrow_id_raw)) {
            $error = "Borrow ID must be a number (example: 1, 2, 3...).";
        } else {

            $borrow_id = intval($borrow_id_raw);
            $today = date("Y-m-d");

            $stmt = $link_id->prepare("
                SELECT id, book_id, user_id, status
                FROM transactions
                WHERE id = ?
                LIMIT 1
            ");
            $stmt->execute([$borrow_id]);
            $trx = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$trx) {
                $error = "Transaction not found.";
            } elseif ($trx["status"] != "borrowed") {
                $error = "This transaction is already returned or not active.";
            } else {

                if (intval($trx["user_id"]) !== intval($_SESSION["user_id"])) {
                    $error = "You cannot return this transaction.";
                } else {

                    $stmtUp = $link_id->prepare("
                        UPDATE transactions
                        SET return_date = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmtUp->execute([$today, "returned", $borrow_id]);

                    if ($condition !== "lost") {
                        $stmtBook = $link_id->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = ?");
                        $stmtBook->execute([$trx["book_id"]]);
                    }

                    $message = "Return processed successfully!";
                }
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
    <title>Return Book - LibFlow</title>
    <link rel="stylesheet" href="return.css">
    <?php include "heading.php" ?>
</head>

<body>
    <div class="form-wrapper">
        <div class="form-container">
            <div class="icon-header">
                <span class="icon">🔄</span>
            </div>

            <h2>Return a Book</h2>
            <p class="subtitle">Enter the Borrow ID to process the return.</p>

            <?php if ($message != "") { ?>
                <p class="msg success-msg">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php } ?>

            <?php if ($error != "") { ?>
                <p class="msg error-msg">
                    <?php echo htmlspecialchars($error); ?>
                </p>
            <?php } ?>

            <form action="return.php" method="POST">
                <div class="form-group">
                    <label>Borrow ID / Transaction No.</label>
                    <input type="text" name="borrow_id" placeholder="e.g. 1" required>
                </div>

                <div class="form-group">
                    <label>Condition of Book</label>
                    <select name="condition">
                        <option value="good">Good Condition</option>
                        <option value="damaged">Damaged</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>

                <button type="submit" class="btn-return">Process Return</button>

                <div class="cancel-link">
                    <a href="dashboard.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>