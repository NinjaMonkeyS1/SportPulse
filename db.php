<?php
// Auto session + remember me
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) && isset($_COOKIE['sp_remember_email'])) {
    $_SESSION['user'] = [
        'name'  => $_COOKIE['sp_remember_name'],
        'email' => $_COOKIE['sp_remember_email'],
    ];
}

// Database
$host   = "localhost";
$dbname = "sportnews";
$user   = "root";
$pass   = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>