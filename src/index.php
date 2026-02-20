<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <?php include "heading.php" ?>
</head>

<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">Lib<span>Flow</span></div>

            <div class="nav-links" id="nav-list">
                <a href="login.php" class="btn-nav">Login</a>
                <a href="register.php" class="btn-nav btn-get-started">Get Started</a>
            </div>

            <div class="menu-toggle" id="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <main class="hero-section">
        <div class="hero-content">
            <h1>Manage your library <br><span>smarter, not harder.</span></h1>
            <p>A streamlined system for librarians and book lovers.</p>
            <div class="hero-btns">
                <a href="login.php" class="btn-primary">Explore Books</a>
                <a href="register.php" class="btn-secondary">Create Account</a>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 LibFlow System. All rights reserved.</p>
    </footer>

    <script src="javascript/burger-menu.js"></script>
</body>

</html>