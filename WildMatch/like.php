<?php
session_start();
require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$liker_id = (int)$_SESSION['user_id'];
$liked_id = (int)($_POST['liked_id'] ?? 0);

// Validate target user exists
if ($liked_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid profile ID']);
    exit();
}

// Prevent self-liking
if ($liker_id === $liked_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot like yourself']);
    exit();
}

try {
    // Check if already liked
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE liker_id = ? AND liked_id = ?");
    $stmt->execute([$liker_id, $liked_id]);
    $already_liked = $stmt->fetch();

    if ($already_liked) {
        // Unlike: delete
        $stmt = $pdo->prepare("DELETE FROM likes WHERE liker_id = ? AND liked_id = ?");
        $stmt->execute([$liker_id, $liked_id]);
        $action = 'unliked';
    } else {
        // Like: insert
        $stmt = $pdo->prepare("INSERT INTO likes (liker_id, liked_id) VALUES (?, ?)");
        $stmt->execute([$liker_id, $liked_id]);
        $action = 'liked';
    }

    // Count total likes for the target profile (for display)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE liked_id = ?");
    $stmt->execute([$liked_id]);
    $total_likes = (int)$stmt->fetchColumn();

    http_response_code(200);
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