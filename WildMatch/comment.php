<?php
session_start();
require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Metod ej tillåten.');
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$target_id = $_POST['target_id'] ?? null;
$content = trim($_POST['content'] ?? '');

// Validate input
if (!$target_id || !ctype_digit($target_id) || empty($content)) {
    $_SESSION['error'] = "Ogiltig begäran.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit();
}

if (strlen($content) > 500) {
    $_SESSION['error'] = "Kommentaren är för lång (max 500 tecken).";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit();
}

try {
    // Optional: prevent self-commenting (not required, but common)
    if ((int)$_SESSION['user_id'] === (int)$target_id) {
        $_SESSION['error'] = "Du kan inte kommentera din egen profil.";
        header("Location: view_profile.php?id=" . (int)$target_id);
        exit();
    }

    // Insert comment
    $stmt = $pdo->prepare("
        INSERT INTO comments (author_id, target_id, content)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $target_id, $content]);

    // Success → redirect back
    header("Location: view_profile.php?id=" . (int)$target_id);
    exit();

} catch (Exception $e) {
    error_log("Comment error: " . $e->getMessage());
    $_SESSION['error'] = "Kunde inte spara kommentaren. Försök igen.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit();
}