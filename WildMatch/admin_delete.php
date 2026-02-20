<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? 0;
if (!$id || !ctype_digit($id)) die("Invalid ID");

try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM likes WHERE liker_id = ? OR liked_id = ?")->execute([$id, $id]);
    $pdo->prepare("DELETE FROM comments WHERE author_id = ? OR target_id = ?")->execute([$id, $id]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    $pdo->commit();
    header("Location: index.php?admin_msg=Profil%20raderad");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Fel vid radering");
}
?>