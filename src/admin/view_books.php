<?php
session_start();
require_once "../db_config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

$stmt = $link_id->prepare("SELECT id, title, author, genre, quantity, cover_image FROM books ORDER BY id DESC");
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - LibFlow</title>
    <?php include "../heading.php"; ?>
    <link rel="stylesheet" href="view_books.css">
</head>

<body>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h2 class="page-title">Book Inventory</h2>
                <p class="page-subtitle">Manage and monitor library stocks</p>
            </div>
            <a href="dashboard.php" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to Dashboard
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Book Details</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($books) == 0): ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                No books found in the inventory.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $row):
                            $img = "../uploads/sample-book.jpg";
                            if (!empty($row["cover_image"])) {
                                $img = "../" . $row["cover_image"];
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="book-info">
                                        <img src="<?php echo $img; ?>" class="book-img" alt="book">
                                        <span class="book-title"><?php echo htmlspecialchars($row["title"]); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row["author"]); ?></td>
                                <td><span class="genre-badge"><?php echo htmlspecialchars($row["genre"]); ?></span></td>
                                <td class="text-center">
                                    <span class="qty-text"><?php echo intval($row["quantity"]); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="action-group">
                                        <a href="edit_book.php?id=<?php echo $row["id"]; ?>" class="btn-edit">Edit</a>
                                        <!-- <a href="delete_book.php?id=<?php echo $row["id"]; ?>" class="btn-delete"
                                            onclick="return confirm('Are you sure you want to delete this book?');">Delete
                                        </a> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>