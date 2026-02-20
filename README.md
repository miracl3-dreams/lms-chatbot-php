📚 LMS Chatbot - PHP

A simple Library Management System (LMS) built with PHP & MySQL, integrated with a chatbot assistant to help users interact with the system.

This project is designed for learning purposes and can serve as a foundation for building a more advanced LMS with AI integration.

🚀 Features

📖 Book Management: Add, Update, Delete, Search books

👤 User / Member Management

📊 Reporting: PDF Generation using Cezpdf

🔐 Login & Authentication System

🤖 Chatbot Integration

🖨 Printable Reports

📂 MySQL Database Structure

books — stores book details

users — stores member/user details

transactions — tracks borrow/return history

(You can extend this based on your LMS features.)

🛠 Built With

PHP 7.3+

MySQL / MariaDB

JavaScript / jQuery

Bootstrap 4/5

XAMPP

Cezpdf (for PDF reports)

🖥 Installation & Setup Guide
1. Install XAMPP

Download XAMPP from https://www.apachefriends.org/index.html

Recommended version: XAMPP 8.1.x with PHP 8.1 (compatible with PHP 7.3+)

Run the installer and choose default options.

Start Apache and MySQL from the XAMPP Control Panel.

Open your browser and go to http://localhost to confirm XAMPP is working.

2. Setup Database

Open phpMyAdmin at http://localhost/phpmyadmin.

Create a new database, for example: lms_db.

Import the provided SQL file (if available) or create tables manually:

books

users

transactions

chat_logs

Update your db_config.php file with your database credentials:

<?php
$host = "localhost";
$db = "lms_db";
$user = "root";
$pass = "";
?>
3. Configure Chatbot API Key

Choose an AI chatbot provider (e.g., OpenAI, Ollama).

Sign up and obtain an API key.

Store your API key in a secure configuration file, e.g., config.php:

<?php
define('CHATBOT_API_KEY', 'YOUR_API_KEY_HERE');
?>

Your LMS chatbot will use this key to interact with the AI service.

💡 Tip: Do not commit your API key to public repositories.

4. Running the LMS

Place the project folder in your XAMPP htdocs directory.

Open your browser and navigate to:

http://localhost/your-project-folder/

Login with your default admin credentials (if any).

Start using the LMS features and test the chatbot.

5. Ongoing Development

This LMS project is actively being developed.

New features and bug fixes are being added continuously.

If you encounter any issues, check the PHP error logs or database connectivity.

Contributions and suggestions are welcome.

⚠ Notes

Make sure PHP 7.3+ is installed (bundled with XAMPP).

PDF reports require the Cezpdf library.

Chatbot integration depends on a valid API key.