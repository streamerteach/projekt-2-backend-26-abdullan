<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$liker_id = (int)$_SESSION['user_id'];
$liked_id = (int)($_POST['liked_id'] ?? 0); // ← Now works!

if ($liked_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid profile ID']);
    exit();
}

if ($liker_id === $liked_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot like yourself']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE liker_id = ? AND liked_id = ?");
    $stmt->execute([$liker_id, $liked_id]);
    $already_liked = $stmt->fetch();

    if ($already_liked) {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE liker_id = ? AND liked_id = ?");
        $stmt->execute([$liker_id, $liked_id]);
        $action = 'unliked';
    } else {
        $stmt = $pdo->prepare("INSERT INTO likes (liker_id, liked_id) VALUES (?, ?)");
        $stmt->execute([$liker_id, $liked_id]);
        $action = 'liked';
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE liked_id = ?");
    $stmt->execute([$liked_id]);
    $total_likes = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'total_likes' => $total_likes
    ]);
} catch (Exception $e) {
    error_log("Like action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>