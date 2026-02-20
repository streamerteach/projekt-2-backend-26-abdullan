<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT username, full_name, email, city, bio, salary, preference FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) die("Användare hittades inte.");
} catch (Exception $e) {
    die("Fel vid hämtning av profil.");
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Min profil – WildMatch</title>
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
                    <li><a href="index.php">Hem</a></li>
                    <li><a href="profile.php" class="active">Min profil</a></li>
                    <li><a href="logout.php">Logga ut</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>Välkommen,
            <?php echo htmlspecialchars($user['full_name']) ?>!
        </h1>

        <section class="info-section">
            <p><strong>Användarnamn:</strong>
                <?php echo htmlspecialchars($user['username']) ?>
            </p>
            <p><strong>E-post:</strong>
                <?php echo htmlspecialchars($user['email']) ?>
            </p>
            <p><strong>Stad:</strong>
                <?php echo htmlspecialchars($user['city'] ?: 'Inte angivet') ?>
            </p>
            <p><strong>Årslön:</strong>
                <?php echo $user['salary'] ? number_format($user['salary'], 0, ',', ' ') . ' kr' : 'Inte angivet' ?>
            </p>
            <p><strong>Preferens:</strong>
                <?php echo htmlspecialchars($user['preference']) ?>
            </p>
            <?php if (! empty($user['bio'])): ?>
            <p><strong>Om mig:</strong><br><em>"
                    <?php echo htmlspecialchars($user['bio']) ?>"
                </em></p>
            <?php endif; ?>
        </section>

        <!-- Edit/Delete Links -->
        <section class="info-section">
            <h2>Hantera din profil</h2>
            <a href="edit_profile.php" class="book-btn">Redigera profil</a>
            <a href="delete_profile.php" class="book-btn" style="background:#c00;">Radera profil</a>
        </section>

        <hr>
        <h2>Ladda upp profilbild</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" accept=".jpg,.jpeg,.png" required>
            <button type="submit" class="book-btn">Ladda upp</button>
        </form>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ! empty($_FILES['fileToUpload']['name'])) {
                echo "<p style='color:#4CAF50;'>Bilduppladdning är inte aktiverad i Projekt 2 — men du kan lägga till det senare.</p>";
            }
        ?>
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