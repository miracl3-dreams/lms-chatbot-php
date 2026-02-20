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

    $book_id = intval($_POST["book_id"] ?? 0);
    $issue_date = date("Y-m-d");

    if ($book_id <= 0) {
        $error = "Please select a book.";
    } else {

        $stmt = $link_id->prepare("SELECT id, quantity FROM books WHERE id = ? LIMIT 1");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            $error = "Book not found.";
        } elseif (intval($book["quantity"]) <= 0) {
            $error = "This book is out of stock.";
        } else {

            $arr_borrow = array();
            $arr_borrow["user_id"] = $_SESSION["user_id"];
            $arr_borrow["book_id"] = $book_id;
            $arr_borrow["issue_date"] = $issue_date;
            $arr_borrow["return_date"] = null;
            $arr_borrow["status"] = "borrowed";

            $result = PDO_InsertRecord($link_id, "transactions", $arr_borrow, false);

            if ($result === true) {

                $stmt2 = $link_id->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ? AND quantity > 0");
                $stmt2->execute([$book_id]);

                $message = "Borrow successful!";
            } else {
                $error = "Borrow failed: " . $result;
            }
        }
    }
}

$stmtBooks = $link_id->prepare("SELECT id, title, author, quantity FROM books WHERE quantity > 0 ORDER BY title ASC");
$stmtBooks->execute();
$books = $stmtBooks->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php include "heading.php" ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow a Book - LibFlow</title>
</head>

<body>
    <div class="form-wrapper">
        <div class="form-container">
            <h2>Borrow a Book</h2>

            <?php if ($message != "") { ?>
                <p style="color: green; text-align:center; margin-bottom:10px;">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php } ?>

            <?php if ($error != "") { ?>
                <p style="color: red; text-align:center; margin-bottom:10px;">
                    <?php echo htmlspecialchars($error); ?>
                </p>
            <?php } ?>

            <form action="borrow.php" method="POST">

                <div class="form-group">
                    <label>Select Book</label>
                    <select name="book_id" required>
                        <option value="">-- Choose a Book --</option>

                        <?php foreach ($books as $b) { ?>
                            <option value="<?php echo $b["id"]; ?>">
                                <?php echo htmlspecialchars($b["title"] . " - " . $b["author"] . " (Stock: " . $b["quantity"] . ")"); ?>
                            </option>
                        <?php } ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Borrower Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION["user_name"] ?? ""); ?>" disabled>
                </div>

                <button type="submit" class="btn-submit">Confirm Borrow</button>

                <p><a href="dashboard.php" style="color: #777; text-decoration: none;">&larr; Back to Home</a></p>
            </form>
        </div>
    </div>
</body>

</html>