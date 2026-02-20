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

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        die("Användare hittades inte.");
    }
} catch (Exception $e) {
    error_log("Edit fetch error: " . $e->getMessage());
    die("Fel vid hämtning av profil.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $salary = (int)($_POST['salary'] ?? 0);
    $preference = $_POST['preference'] ?? 'Alla';
    $current_password = $_POST['current_password'] ?? '';

    // Validate required fields
    if (empty($full_name) || empty($email)) {
        $error = "Namn och e-post är obligatoriska.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ogiltig e-postadress.";
    } else {
        // Check if sensitive fields changed → require password
        $sensitive_changed = (
            $email !== $user['email'] ||
            $salary != $user['salary']
        );

        if ($sensitive_changed) {
            if (empty($current_password)) {
                $error = "Lösenord krävs för att ändra e-post eller lön.";
            } elseif (!password_verify($current_password, $user['password_hash'])) {
                $error = "Felaktigt lösenord.";
            }
        }

        if (!$error) {
            try {
                // Update profile
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, email = ?, city = ?, bio = ?, salary = ?, preference = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $full_name, $email, $city, $bio, $salary, $preference, $user_id
                ]);
                $success = "Profilen uppdaterades!";
                // Refresh user data
                $user = $stmt->fetch() ?: $user;
            } catch (Exception $e) {
                error_log("Profile update error: " . $e->getMessage());
                $error = "Kunde inte spara ändringarna.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redigera profil – WildMatch</title>
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
        <h1>Redigera din profil</h1>

        <?php if ($error): ?>
        <p style="color: #f44336;">
            <?= htmlspecialchars($error) ?>
        </p>
        <?php endif; ?>
        <?php if ($success): ?>
        <p style="color: #4CAF50;">
            <?= htmlspecialchars($success) ?>
        </p>
        <?php endif; ?>

        <form method="POST" class="info-section">
            <p><label>Namn/Rubrik: <input type="text" name="full_name"
                        value="<?= htmlspecialchars($user['full_name']) ?>" required></label></p>
            <p><label>E-post: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                        required></label></p>
            <p><label>Stad: <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>"></label></p>
            <p><label>Årslön: <input type="number" name="salary" value="<?= (int)$user['salary'] ?>" min="0"></label>
            </p>
            <p>
                <label>Preferens:
                    <select name="preference">
                        <option value="Man" <?=$user['preference']==='Man' ? 'selected' : '' ?>>Man</option>
                        <option value="Kvinna" <?=$user['preference']==='Kvinna' ? 'selected' : '' ?>>Kvinna</option>
                        <option value="Båda" <?=$user['preference']==='Båda' ? 'selected' : '' ?>>Båda</option>
                        <option value="Annat" <?=$user['preference']==='Annat' ? 'selected' : '' ?>>Annat</option>
                        <option value="Alla" <?=$user['preference']==='Alla' ? 'selected' : '' ?>>Alla</option>
                    </select>
                </label>
            </p>
            <p><textarea name="bio" placeholder="Berätta om dig..." rows="4"
                    style="width:100%;"><?= htmlspecialchars($user['bio']) ?></textarea></p>

            <!-- Password field appears only if needed -->
            <?php if (!empty($_POST) && (
            ($_POST['email'] ?? '') !== $user['email'] ||
            ((int)$_POST['salary'] ?? 0) != $user['salary']
        )): ?>
            <p><label>Ditt nuvarande lösenord (krävs för att ändra e-post/lön):
                    <input type="password" name="current_password" required>
                </label></p>
            <?php endif; ?>

            <button type="submit" class="book-btn">Spara ändringar</button>
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