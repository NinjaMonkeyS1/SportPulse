<?php
session_start();
require 'db.php';

// Проверка дали потребителят е влязъл и дали имаме изпратено ID на статия
if (!isset($_SESSION['user']) || !isset($_POST['article_id'])) {
    echo json_encode(['success' => false, 'message' => 'Неоторизиран достъп']);
    exit;
}

$uemail = $_SESSION['user']['email'];
$article_id = (int)$_POST['article_id'];

// 1. Проверяваме дали тази статия вече е запазена от този потребител
$stmt = $pdo->prepare("SELECT id FROM saved_articles WHERE user_email = ? AND article_id = ?");
$stmt->execute([$uemail, $article_id]);
$already_saved = $stmt->fetch();

if ($already_saved) {
    // Ако е запазена -> ИЗТРИВАМЕ Я (премахване от запазени)
    $delete = $pdo->prepare("DELETE FROM saved_articles WHERE user_email = ? AND article_id = ?");
    $delete->execute([$uemail, $article_id]);
    echo json_encode(['success' => true, 'status' => 'removed']);
} else {
    // Ако НЕ Е запазена -> ДОБАВЯМЕ Я
    $insert = $pdo->prepare("INSERT INTO saved_articles (user_email, article_id) VALUES (?, ?)");
    $insert->execute([$uemail, $article_id]);
    echo json_encode(['success' => true, 'status' => 'saved']);
}
exit;