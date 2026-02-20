<?php
session_start();
require_once 'config.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (empty($password)) {
        $error = "Lösenord krävs för att radera kontot.";
    } else {
        try {
            // Fetch user to verify password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = "Felaktigt lösenord.";
            } else {
                // Start transaction for data integrity
                $pdo->beginTransaction();

                // Delete related data first (foreign key constraints)
                $pdo->prepare("DELETE FROM likes WHERE liker_id = ? OR liked_id = ?")->execute([$user_id, $user_id]);
                $pdo->prepare("DELETE FROM comments WHERE author_id = ? OR target_id = ?")->execute([$user_id, $user_id]);

                // Delete user
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

                $pdo->commit();

                // Clear session and redirect
                session_destroy();
                $success = "Ditt konto har raderats.";
                header("Location: index.php?deleted=1");
                exit();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Delete profile error: " . $e->getMessage());
            $error = "Kunde inte radera kontot. Försök igen senare.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radera profil – WildMatch</title>
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
                    <li><a href="profile.php">Min profil</a></li>
                    <li><a href="logout.php">Logga ut</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>Radera din profil</h1>
        <p style="color: #ff6666;">
            Varning: Detta raderar ditt konto permanent, inklusive alla kommentarer och gillningar.
        </p>

        <?php if ($error): ?>
        <p style="color: #f44336;">
            <?= htmlspecialchars($error) ?>
        </p>
        <?php endif; ?>

        <form method="POST" class="info-section">
            <p>
                <label for="password">Bekräfta med ditt lösenord för att fortsätta:</label><br>
                <input type="password" id="password" name="password" required
                    style="width: 100%; padding: 0.5rem; margin-top: 0.5rem;">
            </p>
            <button type="submit" class="book-btn" style="background: #c00;">Radera konto</button>
            <a href="profile.php" style="display: inline-block; margin-left: 1rem;">Avbryt</a>
        </form>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 WildMatch</p>
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