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

$message = "";
$error = "";

$book_id = intval($_GET["id"] ?? 0);
if ($book_id <= 0) {
    header("Location: view_books.php");
    exit;
}

$stmtBook = $link_id->prepare("SELECT id, title, author, genre, quantity FROM books WHERE id = ? LIMIT 1");
$stmtBook->execute([$book_id]);
$book = $stmtBook->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: view_books.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $author = trim($_POST["author"] ?? "");
    $genre = trim($_POST["genre"] ?? "");
    $quantity = intval($_POST["quantity"] ?? 0);

    if ($title === "" || $author === "") {
        $error = "Title and Author are required.";
    } elseif ($quantity < 0) {
        $error = "Quantity cannot be negative.";
    } else {

        $stmtUpdate = $link_id->prepare("
            UPDATE books 
            SET title = ?, author = ?, genre = ?, quantity = ?
            WHERE id = ?
        ");

        $ok = $stmtUpdate->execute([
            $title,
            $author,
            $genre,
            $quantity,
            $book_id
        ]);

        if ($ok) {

            $stmtBook = $link_id->prepare("SELECT id, title, author, genre, quantity FROM books WHERE id = ? LIMIT 1");
            $stmtBook->execute([$book_id]);
            $book = $stmtBook->fetch(PDO::FETCH_ASSOC);

            $message = "Book updated successfully!";
        } else {
            $error = "Update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - LibFlow</title>
    <?php include "../heading.php"; ?>
    <link rel="stylesheet" href="edit_book.css">
</head>

<body>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h2 class="page-title">Edit Book</h2>
                <p class="page-subtitle">Update book information and availability</p>
            </div>
        </div>

        <?php if ($message != "") { ?>
            <p style="color: green; margin-bottom: 12px;">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>

        <?php if ($error != "") { ?>
            <p style="color: red; margin-bottom: 12px;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php } ?>

        <div class="form-container">
            <form action="edit_book.php?id=<?php echo $book_id; ?>" method="POST">
                <div class="fields-section">

                    <div class="input-group">
                        <label class="field-label">Book Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($book["title"]); ?>"
                            required>
                    </div>

                    <div class="input-row">
                        <div class="input-group">
                            <label class="field-label">Author</label>
                            <input type="text" name="author" value="<?php echo htmlspecialchars($book["author"]); ?>"
                                required>
                        </div>

                        <div class="input-group">
                            <label class="field-label">Genre</label>
                            <input type="text" name="genre" value="<?php echo htmlspecialchars($book["genre"]); ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="field-label">Stock Quantity</label>
                        <input type="number" name="quantity" value="<?php echo intval($book["quantity"]); ?>" min="0"
                            required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">Update Book Details</button>
                        <button type="button" class="btn-cancel" onclick="window.location.href='view_books.php'">Cancel
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</body>

</html>