<?php
session_start();
require_once 'config.php';

// Default values
$sort = $_GET['sort'] ?? 'newest';
$filter = $_GET['filter'] ?? 'Alla';
$limit = 5;
$offset = $_GET['offset'] ?? 0;

// Build query dynamically
$orderClause = match($sort) {
    'salary' => 'salary DESC, created_at DESC',
    'likes' => '(SELECT COUNT(*) FROM likes WHERE liked_id = users.id) DESC, created_at DESC',
    default => 'created_at DESC'
};

$whereClause = ($filter !== 'Alla') ? 'WHERE preference = ?' : '';
$params = ($filter !== 'Alla') ? [$filter] : [];

try {
    $sql = "SELECT id, username, full_name, city, bio, created_at, salary, preference 
            FROM users $whereClause ORDER BY $orderClause LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    if ($filter !== 'Alla') {
        $stmt->execute(array_merge($params, [$limit, $offset]));
    } else {
        $stmt->execute([$limit, $offset]);
    }
    $users = $stmt->fetchAll();

    // Total count for pagination
    $countSql = "SELECT COUNT(*) FROM users $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($filter !== 'Alla' ? [$filter] : []);
    $totalUsers = $countStmt->fetchColumn();
} catch (Exception $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    $users = [];
    $totalUsers = 0;
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
        <!-- Filter & Sort Controls -->
        <div class="info-section" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <form id="filter-form" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <label>Sortera efter:
                    <select name="sort" onchange="this.form.submit()">
                        <option value="newest" <?=$sort==='newest' ? 'selected' : '' ?>>Nyast</option>
                        <option value="salary" <?=$sort==='salary' ? 'selected' : '' ?>>Högst lön</option>
                        <option value="likes" <?=$sort==='likes' ? 'selected' : '' ?>>Flest gillningar</option>
                    </select>
                </label>
                <label>Filtrera på:
                    <select name="filter" onchange="this.form.submit()">
                        <option value="Alla" <?=$filter==='Alla' ? 'selected' : '' ?>>Alla</option>
                        <option value="Man" <?=$filter==='Man' ? 'selected' : '' ?>>Man</option>
                        <option value="Kvinna" <?=$filter==='Kvinna' ? 'selected' : '' ?>>Kvinna</option>
                        <option value="Båda" <?=$filter==='Båda' ? 'selected' : '' ?>>Båda</option>
                        <option value="Annat" <?=$filter==='Annat' ? 'selected' : '' ?>>Annat</option>
                    </select>
                </label>
            </form>
        </div>

        <h2>Alla profiler (
            <?= $totalUsers ?>)
        </h2>
        <div id="profiles-container" class="feature-grid">
            <?php foreach ($users as $user): ?>
            <div class="feature-card" style="text-align: left; padding: 1.2rem;">
                <h3>
                    <?= htmlspecialchars($user['full_name']) ?>
                </h3>
                <p><strong>Användare:</strong>
                    <?= htmlspecialchars($user['username']) ?>
                </p>
                <p><strong>Stad:</strong>
                    <?= htmlspecialchars($user['city'] ?: 'Inte angivet') ?>
                </p>
                <p><strong>Preferens:</strong>
                    <?= htmlspecialchars($user['preference']) ?>
                </p>
                <?php if (isset($_SESSION['user_id']) && $user['salary']): ?>
                <p><strong>Årslön:</strong>
                    <?= number_format($user['salary'], 0, ',', ' ') ?> kr
                </p>
                <?php endif; ?>
                <p><strong>Medlem sedan:</strong>
                    <?= date('j F Y', strtotime($user['created_at'])) ?>
                </p>
                <?php if (!empty($user['bio'])): ?>
                <p><em>"
                        <?= htmlspecialchars($user['bio']) ?>"
                    </em></p>
                <?php endif; ?>
                <a href="view_profile.php?id=<?= (int)$user['id'] ?>" class="book-btn"
                    style="margin-top: 0.5rem; display: inline-block;">
                    Visa profil
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Loading indicator -->
        <div id="loading" style="text-align: center; margin: 2rem 0; display: none;">
            <p>Laddar fler profiler...</p>
        </div>

        <!-- Hidden state for JS -->
        <div id="pagination-state" data-offset="<?= count($users) ?>" data-total="<?= $totalUsers ?>"
            data-limit="<?= $limit ?>" data-sort="<?= htmlspecialchars($sort) ?>"
            data-filter="<?= htmlspecialchars($filter) ?>">
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 WildMatch. Alla profiler är offentliga för visning.</p>
        </div>
    </footer>

    <script>
        let isLoading = false;

        function loadMoreProfiles() {
            const state = document.getElementById('pagination-state');
            const offset = parseInt(state.dataset.offset);
            const total = parseInt(state.dataset.total);
            const limit = parseInt(state.dataset.limit);
            const sort = state.dataset.sort;
            const filter = state.dataset.filter;

            if (offset >= total || isLoading) return;

            isLoading = true;
            document.getElementById('loading').style.display = 'block';

            fetch(`load_profiles.php?offset=${offset}&limit=${limit}&sort=${encodeURIComponent(sort)}&filter=${encodeURIComponent(filter)}`)
                .then(response => response.text())
                .then(html => {
                    if (html.trim()) {
                        document.getElementById('profiles-container').insertAdjacentHTML('beforeend', html);
                        state.dataset.offset = offset + limit;
                    }
                    document.getElementById('loading').style.display = 'none';
                    isLoading = false;
                })
                .catch(() => {
                    document.getElementById('loading').style.display = 'none';
                    isLoading = false;
                });
        }

        // Trigger on scroll near bottom
        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
                loadMoreProfiles();
            }
        });

        // Mobile menu 
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