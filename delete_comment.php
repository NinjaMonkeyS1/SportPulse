<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['article_id'])) {
    $id        = (int) $_GET['id'];
    $articleId = (int) $_GET['article_id'];
    $uemail    = $_SESSION['user']['email'];

    // Only allow deleting your own comment
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_email = ?");
    $stmt->execute([$id, $uemail]);

    header("Location: article.php?id=" . $articleId . "#comments");
    exit;
}

header("Location: index.php");
exit;
?>