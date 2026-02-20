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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $author = trim($_POST["author"] ?? "");
    $genre = trim($_POST["genre"] ?? "");
    $quantity = intval($_POST["quantity"] ?? 1);

    if ($title == "" || $author == "") {
        $error = "Title and Author are required.";
    } else {
        $coverPath = "";
        if (isset($_FILES["cover_image"]) && $_FILES["cover_image"]["error"] == 0) {
            $uploadDir = "../uploads/book_covers/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION));
            $allowed = ["jpg", "jpeg", "png", "webp"];

            if (!in_array($ext, $allowed)) {
                $error = "Invalid image format. Only JPG, PNG, WEBP allowed.";
            } else {
                $newFileName = "cover_" . time() . "_" . rand(1000, 9999) . "." . $ext;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $targetPath)) {
                    $coverPath = "uploads/book_covers/" . $newFileName;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }

        if ($error == "") {
            $arr_book = [
                "title" => $title,
                "author" => $author,
                "genre" => $genre,
                "cover_image" => $coverPath,
                "quantity" => $quantity
            ];

            $result = PDO_InsertRecord($link_id, "books", $arr_book, false);
            if ($result === true) {
                $message = "Book added successfully!";
            } else {
                $error = "Insert failed: " . $result;
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
    <title>Add New Book - LibFlow</title>
    <?php include "../heading.php" ?>
    <link rel="stylesheet" href="add_book.css">
</head>

<body>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h2 class="page-title">Add New Book</h2>
                <p class="page-subtitle">Register a new title to the library system</p>
            </div>
            <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
        </div>

        <div class="form-container">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="add_book.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="image-section">
                        <label class="field-label">Book Cover</label>
                        <div class="image-preview-wrapper">
                            <img id="preview" src="../uploads/sample-book.jpg" alt="Preview">
                        </div>
                        <input type="file" name="cover_image" id="cover_image" accept="image/*"
                            onchange="previewImage(event)">
                        <label for="cover_image" class="btn-upload">Select Image</label>
                    </div>

                    <div class="fields-section">
                        <div class="input-group">
                            <label class="field-label">Book Title</label>
                            <input type="text" name="title" placeholder="e.g. The Great Gatsby" required>
                        </div>

                        <div class="input-row">
                            <div class="input-group">
                                <label class="field-label">Author</label>
                                <input type="text" name="author" placeholder="Enter author name" required>
                            </div>
                            <div class="input-group">
                                <label class="field-label">Genre</label>
                                <input type="text" name="genre" placeholder="e.g. Fiction">
                            </div>
                        </div>

                        <div class="input-group">
                            <label class="field-label">Initial Quantity</label>
                            <input type="number" name="quantity" value="1" min="1">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">Register Book</button>
                            <button type="button" class="btn-cancel"
                                onclick="window.location.href='dashboard.php'">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('preview');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>

</html>