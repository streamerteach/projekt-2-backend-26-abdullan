<?php
session_start();
require_once 'config.php';

$offset = $_GET['offset'] ?? 0;
$limit = $_GET['limit'] ?? 5;
$sort = $_GET['sort'] ?? 'newest';
$filter = $_GET['filter'] ?? 'Alla';

if (!ctype_digit($offset) || !ctype_digit($limit)) exit();

$orderClause = match($sort) {
    'salary' => 'salary DESC, created_at DESC',
    'likes' => '(SELECT COUNT(*) FROM likes WHERE liked_id = users.id) DESC, created_at DESC',
    default => 'created_at DESC'
};

$whereClause = ($filter !== 'Alla') ? 'WHERE preference = ?' : '';
$params = ($filter !== 'Alla') ? [$filter] : [];
array_push($params, (int)$limit, (int)$offset);

try {
$sql = "SELECT id, username, full_name, city, bio, created_at, salary, preference 
        FROM users $whereClause ORDER BY $orderClause LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

foreach ($users as $user):
?>
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
<?php
endforeach;
} catch (Exception $e) {
error_log("AJAX profile load error: " . $e->getMessage());
}
?>