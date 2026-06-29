<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId = (int) $_POST['article_id'];
    $comment   = trim($_POST['comment']);
    $name      = $_SESSION['user']['name'];
    $email     = $_SESSION['user']['email'];

    if ($comment !== '' && $articleId > 0) {
        $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_name, user_email, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$articleId, $name, $email, $comment]);
    }

    header("Location: article.php?id=" . $articleId . "#comments");
    exit;
}

header("Location: index.php");
exit;
?>