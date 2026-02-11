<?php
session_start();
require_once 'config.php';

// Validate and fetch target profile
$user_id = $_GET['id'] ?? null;
if (!$user_id || !ctype_digit($user_id)) {
    http_response_code(400);
    die("Ogiltigt profil-ID.");
}

try {
    // Fetch target user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $target_user = $stmt->fetch();

    if (!$target_user) {
        http_response_code(404);
        die("Profilen hittades inte.");
    }

    // Check if current user has liked this profile
    $is_liked = false;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE liker_id = ? AND liked_id = ?");
        $stmt->execute([$_SESSION['user_id'], $user_id]);
        $is_liked = (bool)$stmt->fetch();
    }

    // Count total likes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE liked_id = ?");
    $stmt->execute([$user_id]);
    $total_likes = (int)$stmt->fetchColumn();

    // Fetch comments on this profile
    $stmt = $pdo->prepare("
        SELECT c.content, c.created_at, u.username 
        FROM comments c
        JOIN users u ON c.author_id = u.id
        WHERE c.target_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $comments = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Profile view error: " . $e->getMessage());
    die("Ett fel uppstod vid visning av profilen.");
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($target_user['full_name']) ?> – WildMatch</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo-text">WildMatch</div>
        <nav>
            <ul>
                <li><a href="index.php">Hem</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Logga in</a></li>
                <?php else: ?>
                    <li><a href="profile.php">Min profil</a></li>
                    <li><a href="logout.php">Logga ut</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="container">
    <h1><?= htmlspecialchars($target_user['full_name']) ?></h1>

    <!-- Profile Info -->
    <section class="info-section">
        <p><strong>Användarnamn:</strong> <?= htmlspecialchars($target_user['username']) ?></p>
        <p><strong>Stad:</strong> <?= htmlspecialchars($target_user['city'] ?: 'Inte angivet') ?></p>
        <p><strong>Preferens:</strong> <?= htmlspecialchars($target_user['preference']) ?></p>
        <p><strong>Medlem sedan:</strong> <?= date('j F Y', strtotime($target_user['created_at'])) ?></p>

        <?php if (!empty($target_user['bio'])): ?>
            <p><strong>Om mig:</strong><br>
            <em>"<?= htmlspecialchars($target_user['bio']) ?>"</em></p>
        <?php endif; ?>

        <!-- Show sensitive info only if logged in -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <p><strong>E-post:</strong> <?= htmlspecialchars($target_user['email']) ?></p>
            <?php if ($target_user['salary']): ?>
                <p><strong>Årslön:</strong> <?= number_format($target_user['salary'], 0, ',', ' ') ?> kr</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Like Button (only for logged-in users, not self) -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $target_user['id']): ?>
        <div style="margin: 1rem 0;">
            <button id="like-btn" 
                    data-action="<?= $is_liked ? 'unlike' : 'like' ?>"
                    class="book-btn">
                <?= $is_liked ? 'Gilla inte längre' : 'Gilla denna profil' ?>
            </button>
            <span id="like-count" style="margin-left: 0.5rem; color:#aaa;">
                (<?= $total_likes ?>)
            </span>
        </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <section class="info-section">
        <h2>Kommentarer (<?= count($comments) ?>)</h2>
        <?php if (empty($comments)): ?>
            <p>Inga kommentarer än.</p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div style="border-bottom:1px solid #333; padding:0.5rem 0; margin:0.5rem 0;">
                    <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                    <?= htmlspecialchars($comment['content']) ?>
                    <br><small><?= date('j/n Y H:i', strtotime($comment['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Add Comment Form (logged-in only) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" action="comment.php" style="margin-top: 1rem;">
                <input type="hidden" name="target_id" value="<?= (int)$user_id ?>">
                <textarea name="content" placeholder="Skriv en kommentar..." required rows="3" style="width:100%;"></textarea>
                <button type="submit" class="book-btn" style="margin-top:0.5rem;">Skicka</button>
            </form>
        <?php else: ?>
            <p><a href="login.php" style="color:#ff0000;">Logga in för att kommentera.</a></p>
        <?php endif; ?>
    </section>

    <!-- Owner Controls -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $target_user['id']): ?>
        <section class="info-section">
            <h2>Dina alternativ</h2>
            <a href="edit_profile.php" class="book-btn">Redigera profil</a>
            <a href="delete_profile.php" class="book-btn" style="background:#c00;">Radera profil</a>
        </section>
    <?php endif; ?>
</main>

<footer>
    <div class="container">
        <p>&copy; 2026 WildMatch</p>
    </div>
</footer>

<!-- Like/Unlike AJAX Integration -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const likeBtn = document.getElementById('like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const likedId = <?= (int)$target_user['id'] ?>;
            const action = likeBtn.dataset.action;

            try {
                const res = await fetch('like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ liked_id: likedId })
                });
                const data = await res.json();

                if (data.success) {
                    if (data.action === 'liked') {
                        likeBtn.textContent = 'Gilla inte längre';
                        likeBtn.dataset.action = 'unlike';
                    } else {
                        likeBtn.textContent = 'Gilla denna profil';
                        likeBtn.dataset.action = 'like';
                    }
                    document.getElementById('like-count').textContent = '(' + data.total_likes + ')';
                } else {
                    alert(data.message || 'Fel vid gillande');
                }
            } catch (err) {
                console.error(err);
                alert('Nätverksfel — försök igen.');
            }
        });
    }
});
</script>
</body>
</html>