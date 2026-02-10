<?php
require_once 'config.php';

// Prevent already logged-in users from accessing login page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Användarnamn och lösenord krävs.";
    } else {
        try {
            // Fetch user by username
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Verify password
            if ($user && password_verify($password, $user['password_hash'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect to profile or homepage
                header("Location: index.php");
                exit();
            } else {
                $error = "Felaktigt användarnamn eller lösenord.";
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Ett tekniskt fel inträffade. Försök igen senare.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Logga in – WildMatch</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo-text">WildMatch</div>
        <nav><ul><li><a href="index.php">Tillbaka</a></li></ul></nav>
    </div>
</header>
<main class="container">
    <h1>Logga in</h1>

    <?php if ($error): ?>
        <p style="color: #f44336;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="info-section">
            <p><label>Användarnamn: <input type="text" name="username" required autofocus></label></p>
            <p><label>Lösenord: <input type="password" name="password" required></label></p>
            <button type="submit" class="book-btn">Logga in</button>
            <p style="margin-top: 1rem;">
                Ingen konto? <a href="register.php" style="color:#ff0000;">Registrera dig</a>
            </p>
        </div>
    </form>
</main>
</body>
</html>