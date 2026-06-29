<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        
        // Записваме потребителя и неговата роля в сесията
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'] // Ролята (admin, author и т.н.)
        ];
         header("Location: index.php");
        // "Запомни ме" функционалност
        if (isset($_POST['remember'])) {
            $cookieTime = time() + (30 * 24 * 60 * 60);
            setcookie('sp_remember_email', $user['email'], $cookieTime, '/');
            setcookie('sp_remember_name',  $user['name'],  $cookieTime, '/');
        }

        header("Location: index.php");
        exit;
    } else {
        // Грешка при грешен имейл или парола
        $error = "Грешен имейл или парола.";
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Inter', sans-serif; min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; background: #fff; }

/* LEFT PANEL */
.left-panel { background: #e63946; display: flex; flex-direction: column; justify-content: space-between; padding: 48px; position: relative; overflow: hidden; }
.left-panel::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: rgba(255,255,255,0.08); border-radius: 50%; }
.left-panel::after { content: ''; position: absolute; bottom: -80px; left: -80px; width: 300px; height: 300px; background: rgba(255,255,255,0.06); border-radius: 50%; }
.brand { display: flex; align-items: center; gap: 10px; text-decoration: none; position: relative; z-index: 1; }
.brand-dot { width: 10px; height: 10px; background: #fff; border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.5; transform:scale(0.7); } }
.brand-name { font-size: 24px; font-weight: 900; color: #fff; letter-spacing: -0.5px; }
.left-content h1 { font-size: 42px; font-weight: 900; color: #fff; line-height: 1.1; letter-spacing: -1px; margin-bottom: 16px; }
.left-content p { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.6; max-width: 340px; }

/* RIGHT PANEL */
.right-panel { display: flex; flex-direction: column; justify-content: center; padding: 60px 64px; background: #fff; }
.right-panel h2 { font-size: 28px; font-weight: 800; color: #0f1117; letter-spacing: -0.5px; margin-bottom: 6px; }
.error-msg { background: #fff0f1; border: 1.5px solid #fca5a5; color: #e63946; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 7px; }
.form-group input { width: 100%; background: #f8f9fc; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 12px 16px; color: #0f1117; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; }
.btn-submit { width: 100%; background: #e63946; color: #fff; border: none; padding: 13px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s; margin-bottom: 20px; }
.btn-submit:hover { background: #c1121f; }
.back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #6b7280; text-decoration: none; margin-bottom: 32px; }
</style>
</head>
<body>

<div class="left-panel">
    <a href="index.php" class="brand">
        <div class="brand-dot"></div>
        <span class="brand-name">SportPulse</span>
    </a>
    <div class="left-content">
        <h1>Вашата ежедневна спортна доза.</h1>
        <p>Резултати на живо, последни новини и задълбочен анализ от всеки спорт на планетата.</p>
    </div>
</div>

<div class="right-panel">
    <a href="index.php" class="back-link">← Обратно към SportPulse</a>
    <h2>Welcome back 👋</h2>
    <?php if ($error): ?>
        <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Имейл</label>
            <input type="email" name="email" required autofocus>
        </div>
        <div class="form-group">
            <label>Парола</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn-submit">Вход →</button>
    </form>
</div>

</body>
</html>