<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $salary = (int)($_POST['salary'] ?? 0);
    $preference = $_POST['preference'] ?? 'Alla';

    // Validation
    if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
        $error = "All fält är obligatoriska.";
    } elseif ($password !== $confirm_password) {
        $error = "Lösenorden matchar inte.";
    } elseif (strlen($password) < 6) {
        $error = "Lösenordet måste vara minst 6 tecken.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ogiltig e-postadress.";
    } else {
        try {
            // Check if username/email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "Användarnamn eller e-post finns redan.";
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, full_name, email, password_hash, city, bio, salary, preference)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $username, $full_name, $email, $password_hash,
                    $city, $bio, $salary, $preference
                ]);

                $success = "Kontot skapades! Du kan nu logga in.";
            }
        } catch (Exception $e) {
            $error = "Ett fel uppstod. Försök igen senare.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Registrera – WildMatch</title>
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
    <h1>Skapa konto</h1>

    <?php if ($error): ?>
        <p style="color: #f44336;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: #4CAF50;"><?= htmlspecialchars($success) ?></p>
        <a href="login.php" class="book-btn">Logga in</a>
    <?php else: ?>
    <form method="POST">
        <div class="info-section">
            <p><label>Användarnamn: <input type="text" name="username" required></label></p>
            <p><label>Namn/Rubrik: <input type="text" name="full_name" required></label></p>
            <p><label>E-post: <input type="email" name="email" required></label></p>
            <p><label>Lösenord: <input type="password" name="password" minlength="6" required></label></p>
            <p><label>Bekräfta lösenord: <input type="password" name="confirm_password" required></label></p>
            <p><label>Stad: <input type="text" name="city"></label></p>
            <p><label>Årslön: <input type="number" name="salary" min="0"></label></p>
            <p>
                <label>Preferens:
                    <select name="preference">
                        <option value="Man">Man</option>
                        <option value="Kvinna">Kvinna</option>
                        <option value="Båda">Båda</option>
                        <option value="Annat">Annat</option>
                        <option value="Alla" selected>Alla</option>
                    </select>
                </label>
            </p>
            <p><textarea name="bio" placeholder="Berätta om dig..." rows="4" style="width:100%;"></textarea></p>
            <button type="submit" class="book-btn">Registrera</button>
        </div>
    </form>
    <?php endif; ?>
</main>
</body>
</html>