<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// IKAW NA BAHALA GUMASTOS SA OPENAI API KEY MO PARA SA CHATGPT FUNCTIONALITY.
// define("OPENAI_API_KEY", "");

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

$intent = null;

if (preg_match('/who\s+borrowed\s+(.+)/i', $messageRaw)) {
    $intent = "WHO_BORROWED_TITLE";
} elseif (preg_match('/how\s+many\s+borrowed|total\s+borrowed|borrowed\s+books/i', $message)) {
    $intent = "TOTAL_BORROWED";
} elseif (preg_match('/available\s+books|list\s+available|show\s+available/i', $message)) {
    $intent = "LIST_AVAILABLE";
} elseif (preg_match('/list\s+all\s+books|show\s+all\s+books|all\s+books/i', $message)) {
    $intent = "LIST_ALL_BOOKS";
} elseif (preg_match('/total\s+books|how\s+many\s+books/i', $message)) {
    $intent = "TOTAL_BOOKS";
} elseif (preg_match('/active\s+users|total\s+users|how\s+many\s+users/i', $message)) {
    $intent = "TOTAL_USERS";
} elseif (preg_match('/low\s+stock|low\s+inventory|nearly\s+out|out\s+of\s+stock/i', $message)) {
    $intent = "LOW_STOCK";
} elseif (preg_match('/my\s+borrowed|what\s+did\s+i\s+borrow|my\s+loans/i', $message)) {
    $intent = "MY_BORROWED";
}

switch ($intent) {

    case "TOTAL_BOOKS":
        echo json_encode(["reply" => totalBooks($link_id)]);
        exit;

    case "TOTAL_USERS":
        echo json_encode(["reply" => totalUsers($link_id)]);
        exit;

    case "TOTAL_BORROWED":
        echo json_encode(["reply" => totalBorrowed($link_id)]);
        exit;

    case "LIST_ALL_BOOKS":
        echo json_encode(["reply" => listAllBooks($link_id)]);
        exit;

    case "LIST_AVAILABLE":
        echo json_encode(["reply" => listAvailableBooks($link_id)]);
        exit;

    case "LOW_STOCK":
        echo json_encode(["reply" => lowStockBooks($link_id)]);
        exit;

    case "MY_BORROWED":
        echo json_encode(["reply" => myBorrowedBooks($link_id, intval($_SESSION["user_id"]))]);
        exit;

    case "WHO_BORROWED_TITLE":
        echo json_encode(["reply" => whoBorrowedTitle($link_id, $messageRaw)]);
        exit;

    default:
        echo json_encode(["reply" => askChatGPT($messageRaw)]);
        exit;
}

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
    $reply .= "\nTip: I only show 10 books at a time.";
    return $reply;
}

function listAvailableBooks($db)
{
    $stmt = $db->prepare("SELECT title, author, quantity FROM books WHERE quantity > 0 ORDER BY title ASC LIMIT 10");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows)
        return "No available books right now (all out of stock).";

    $reply = "Available books right now:\n\n";
    foreach ($rows as $r) {
        $reply .= "• {$r["title"]} — {$r["author"]} (Available: {$r["quantity"]})\n";
    }
    $reply .= "\nTip: I only show 10 available books at a time.";
    return $reply;
}

function lowStockBooks($db)
{
    $threshold = 3;
    $stmt = $db->prepare("SELECT title, author, quantity FROM books WHERE quantity <= ? ORDER BY quantity ASC, title ASC LIMIT 10");
    $stmt->execute([$threshold]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows)
        return "Good news! No low-stock books (≤ {$threshold}) right now.";

    $reply = "Low stock books (≤ {$threshold}):\n\n";
    foreach ($rows as $r) {
        $reply .= "• {$r["title"]} — {$r["author"]} (Stock: {$r["quantity"]})\n";
    }
    return $reply;
}

function myBorrowedBooks($db, $userId)
{
    $stmt = $db->prepare("
        SELECT t.id, t.issue_date, b.title, b.author
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
        return "Please type: Who borrowed <book title>.";

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
        return "I can't find any transaction for '{$title}'.";

    return "Latest transaction for '{$title}':\nBorrower: {$row["name"]}\nIssued: {$row["issue_date"]}\nStatus: {$row["status"]}";
}
function askChatGPT($userMessage)
{
    $url = "https://api.openai.com/v1/chat/completions";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "You are a helpful assistant for the LibFlow library system. Answer only library-related questions."],
            ["role" => "user", "content" => $userMessage]
        ],
        "temperature" => 0.5
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        // "Authorization: Bearer " . OPENAI_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);

    return $json["choices"][0]["message"]["content"] ?? "Sorry, I cannot answer that right now.";
}
