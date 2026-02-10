<?php
session_start();
require_once 'config.php';

// Fetch all users (public info only)
try {
    $stmt = $pdo->query("SELECT id, username, full_name, city, bio, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WildMatch – Hitta din match</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo-text">WildMatch</div>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Hem</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Logga in</a></li>
                    <li><a href="register.php">Registrera</a></li>
                <?php else: ?>
                    <li><a href="profile.php">Min profil</a></li>
                    <li><a href="logout.php">Logga ut</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h1 class="fraktur">Hitta din äventyrs-match</h1>
        <p>Utforska profiler av människor som delar din passion för naturen.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="book-btn">Bli medlem idag</a>
        <?php endif; ?>
    </div>
</section>

<main class="container">
    <h2>Alla profiler (<?= count($users) ?>)</h2>

    <?php if (empty($users)): ?>
        <p>Inga profiler ännu. <a href="register.php" style="color:#ff0000;">Skapa ett konto!</a></p>
    <?php else: ?>
        <div class="feature-grid">
            <?php foreach ($users as $user): ?>
            <div class="feature-card" style="text-align: left; padding: 1.2rem;">
                <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                <p><strong>Användare:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Stad:</strong> <?= htmlspecialchars($user['city'] ?: 'Inte angivet') ?></p>
                <p><strong>Medlem sedan:</strong> <?= date('j F Y', strtotime($user['created_at'])) ?></p>
                <?php if (!empty($user['bio'])): ?>
                    <p><em>"<?= htmlspecialchars($user['bio']) ?>"</em></p>
                <?php endif; ?>
                <a href="view_profile.php?id=<?= (int)$user['id'] ?>" class="book-btn" style="margin-top: 0.5rem; display: inline-block;">
                    Visa profil
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer>
    <div class="container">
        <p>&copy; 2026 WildMatch. Alla profiler är offentliga för visning.</p>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('nav ul');
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
});
</script>
</body>
</html>