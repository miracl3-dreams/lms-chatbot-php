<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../db_config.php";
require_once "../include/lx.pdodb.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["reply" => "Please login first."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["reply" => "Invalid request."]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$messageRaw = trim($input["message"] ?? "");

if ($messageRaw === "") {
    echo json_encode(["reply" => "Please type a message."]);
    exit;
}

$message = strtolower($messageRaw);
$userId = intval($_SESSION["user_id"]);

$intentMap = [
    "WHO_BORROWED_TITLE" => '/who\s+borrowed\s+(.+)/i',
    "TOTAL_BORROWED" => '/how\s+many\s+borrowed|total\s+borrowed|borrowed\s+books/i',
    "LIST_AVAILABLE" => '/available\s+books|list\s+available|show\s+available/i',
    "LIST_ALL_BOOKS" => '/list\s+all\s+books|show\s+all\s+books|all\s+books/i',
    "TOTAL_BOOKS" => '/total\s+books|how\s+many\s+books/i',
    "TOTAL_USERS" => '/active\s+users|total\s+users|how\s+many\s+users/i',
    "LOW_STOCK" => '/low\s+stock|low\s+inventory|nearly\s+out|out\s+of\s+stock/i',
    "MY_BORROWED" => '/my\s+borrowed|what\s+did\s+i\s+borrow|my\s+loans/i'
];

$intent = null;

foreach ($intentMap as $key => $pattern) {
    if (preg_match($pattern, $messageRaw)) {
        $intent = $key;
        break;
    }
}

switch ($intent) {
    case "TOTAL_BOOKS":
        $reply = totalBooks($link_id);
        break;
    case "TOTAL_USERS":
        $reply = totalUsers($link_id);
        break;
    case "TOTAL_BORROWED":
        $reply = totalBorrowed($link_id);
        break;
    case "LIST_ALL_BOOKS":
        $reply = listAllBooks($link_id);
        break;
    case "LIST_AVAILABLE":
        $reply = listAvailableBooks($link_id);
        break;
    case "LOW_STOCK":
        $reply = lowStockBooks($link_id);
        break;
    case "MY_BORROWED":
        $reply = myBorrowedBooks($link_id, $userId);
        break;
    case "WHO_BORROWED_TITLE":
        $reply = whoBorrowedTitle($link_id, $messageRaw);
        break;
    default:
        $reply = askOllama($messageRaw);
        break;
}

echo json_encode(["reply" => $reply]);
exit;

function totalBooks($db)
{
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM books");
    $stmt->execute();
    $total = intval($stmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0);
    return "Total books in the library: {$total}.";
}

function totalUsers($db)
{
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'user'");
    $stmt->execute();
    $total = intval($stmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0);
    return "Total active users: {$total}.";
}

function totalBorrowed($db)
{
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM transactions WHERE status = 'borrowed'");
    $stmt->execute();
    $total = intval($stmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0);
    return "Total borrowed books right now: {$total}.";
}

function listAllBooks($db)
{
    $stmt = $db->prepare("SELECT title, author, quantity FROM books ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows)
        return "No books found in inventory.";

    $reply = "Here are the latest books in inventory:\n\n";
    foreach ($rows as $r) {
        $reply .= "• {$r["title"]} — {$r["author"]} (Stock: {$r["quantity"]})\n";
    }
    return $reply;
}

function listAvailableBooks($db)
{
    $stmt = $db->prepare("SELECT title, author, quantity FROM books WHERE quantity > 0 ORDER BY title ASC LIMIT 10");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows)
        return "No available books right now.";

    $reply = "Available books right now:\n\n";
    foreach ($rows as $r) {
        $reply .= "• {$r["title"]} — {$r["author"]} (Available: {$r["quantity"]})\n";
    }
    return $reply;
}

function lowStockBooks($db)
{
    $threshold = 3;
    $stmt = $db->prepare("SELECT title, author, quantity FROM books WHERE quantity <= ? ORDER BY quantity ASC LIMIT 10");
    $stmt->execute([$threshold]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows)
        return "No low-stock books right now.";

    $reply = "Low stock books (≤ {$threshold}):\n\n";
    foreach ($rows as $r) {
        $reply .= "• {$r["title"]} — {$r["author"]} (Stock: {$r["quantity"]})\n";
    }
    return $reply;
}

function myBorrowedBooks($db, $userId)
{
    $stmt = $db->prepare("
        SELECT t.issue_date, b.title, b.author
        FROM transactions t
        INNER JOIN books b ON b.id = t.book_id
        WHERE t.user_id = ? AND t.status = 'borrowed'
        ORDER BY t.id DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows)
        return "You currently have no borrowed books.";

    $reply = "Here are your active borrowed books:\n\n";
    foreach ($rows as $r) {
        $reply .= "• {$r["title"]} — {$r["author"]} (Borrowed: {$r["issue_date"]})\n";
    }
    return $reply;
}

function whoBorrowedTitle($db, $messageRaw)
{
    $title = trim(preg_replace('/who\s+borrowed\s+/i', '', $messageRaw));
    if ($title === "")
        return "Please type: Who borrowed <book title>?";

    $stmt = $db->prepare("
        SELECT u.name, t.issue_date, t.status
        FROM transactions t
        INNER JOIN users u ON u.id = t.user_id
        INNER JOIN books b ON b.id = t.book_id
        WHERE b.title LIKE ?
        ORDER BY t.id DESC
        LIMIT 1
    ");
    $stmt->execute(["%{$title}%"]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row)
        return "No transaction found for '{$title}'.";

    return "Latest transaction for '{$title}':\nBorrower: {$row["name"]}\nIssued: {$row["issue_date"]}\nStatus: {$row["status"]}";
}

function askOllama($userMessage)
{
    $ch = curl_init("http://localhost:11434/api/generate");

    $payload = [
        "model" => "llama3:8b",
        "prompt" => "You are LibFlow library assistant. Only answer library-related questions clearly.\n\nUser: " . $userMessage,
        "stream" => false
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 60
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return "AI service unavailable.";
    }

    curl_close($ch);

    $json = json_decode($result, true);
    return trim($json["response"] ?? "AI did not return a response.");
}