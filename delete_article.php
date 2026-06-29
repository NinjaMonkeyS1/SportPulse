<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Delete the image file too, if it exists
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['image'])) {
        $imgPath = '../' . $row['image'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$id]);
}

// Redirect back to wherever the delete was triggered from
if (isset($_GET['redirect']) && $_GET['redirect'] === 'home') {
    header("Location: ../index.php");
} else {
    header("Location: add_article.php");
}
exit;
?>