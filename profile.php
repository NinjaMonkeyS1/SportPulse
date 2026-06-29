<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$uname   = $_SESSION['user']['name'];
$uemail  = $_SESSION['user']['email'];
$parts   = explode(' ', trim($uname));
$initials = strtoupper(substr($parts[0],0,1) . (count($parts)>1 ? substr($parts[count($parts)-1],0,1) : substr($parts[0],1,1)));

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newName  = trim($_POST['name']);
    $newEmail = trim($_POST['email']);
    try {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE email=?");
        $stmt->execute([$newName, $newEmail, $uemail]);
        $_SESSION['user']['name']  = $newName;
        $_SESSION['user']['email'] = $newEmail;
        $uname  = $newName;
        $uemail = $newEmail;
        $success = "Profile updated!";
    } catch (PDOException $e) {
        $error = "Could not update profile.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$uemail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (strlen($new) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->execute([$hashed, $uemail]);
        $success = "Password changed successfully!";
    }
}

// Броене на авторските статии
$stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE author=?");
$stmt->execute([$uname]);
$articleCount = $stmt->fetchColumn();

// Взимане на последните 5 статии на автора
$stmt = $pdo->prepare("SELECT * FROM articles WHERE author=? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$uname]);
$myArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Броене на запазените статии
$stmtSaved = $pdo->prepare("SELECT COUNT(*) FROM saved_articles WHERE user_email = ?");
$stmtSaved->execute([$uemail]);
$savedCount = $stmtSaved->fetchColumn();

// Динамичен формат на датата от потребителските настройки
$date_format_setting = 'DD/MM/YYYY'; 
$stmt_settings = $pdo->prepare("SELECT dateformat FROM user_settings WHERE user_email = ?");
$stmt_settings->execute([$uemail]);
$user_settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);
if ($user_settings && !empty($user_settings['dateformat'])) {
    $date_format_setting = $user_settings['dateformat'];
}

switch ($date_format_setting) {
    case 'MM/DD/YYYY': $php_profile_date = 'm/d/Y'; break;
    case 'YYYY-MM-DD': $php_profile_date = 'Y-m-d'; break;
    case 'DD/MM/YYYY':
    default:           $php_profile_date = 'd/m/Y'; break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#f8f9fc; color:#0f1117; min-height:100vh; }
::selection { background:#e63946; color:#fff; }

/* NAV */
nav {
  background:#fff; border-bottom:1px solid #e5e7eb;
  position:sticky; top:0; z-index:100;
  box-shadow:0 1px 0 #e5e7eb;
}
.nav-inner {
  max-width:1200px; margin:0 auto;
  display:flex; align-items:center;
  padding:0 24px; height:60px; gap:16px;
}
.logo {
  font-size:20px; font-weight:900; text-decoration:none;
  color:#0f1117; display:flex; align-items:center; gap:8px;
}
.logo span { color:#e63946; }
.logo-dot { width:8px; height:8px; background:#e63946; border-radius:50%; animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.5;transform:scale(0.7);} }
.nav-right { margin-left:auto; display:flex; align-items:center; gap:10px; }
.nav-user { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:#0f1117; }
.nav-avatar {
  width:32px; height:32px; border-radius:50%;
  background:#e63946; color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:12px; font-weight:800;
}
.nav-link {
  font-size:13px; font-weight:500; color:#6b7280;
  text-decoration:none; padding:6px 12px; border-radius:8px;
  transition:all 0.15s;
}
.nav-link:hover { background:#f0f2f7; color:#0f1117; }
.nav-link.danger { color:#e63946; }
.nav-link.danger:hover { background:#fff0f1; }

/* LAYOUT */
.page { max-width:1100px; margin:0 auto; padding:32px 24px; display:grid; grid-template-columns:280px 1fr; gap:24px; align-items:start; }

/* SIDEBAR */
.sidebar { display:flex; flex-direction:column; gap:16px; }

.profile-card {
  background:#fff; border:1px solid #e5e7eb; border-radius:16px;
  padding:28px; text-align:center;
  box-shadow:0 2px 12px rgba(0,0,0,0.04);
  animation: fadeUp 0.3s ease;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);} }

.big-avatar {
  width:80px; height:80px; border-radius:50%;
  background:linear-gradient(135deg,#e63946,#ff6b6b);
  color:#fff; display:flex; align-items:center; justify-content:center;
  font-size:30px; font-weight:900; margin:0 auto 14px;
  box-shadow:0 8px 24px rgba(230,57,70,0.25);
}
.profile-card h3 { font-size:18px; font-weight:800; margin-bottom:4px; }
.profile-card p { font-size:13px; color:#6b7280; margin-bottom:20px; }

.stats-row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; }
.stat {
  background:#f8f9fc; border-radius:10px; padding:10px 6px; text-align:center;
}
.stat-num { font-size:20px; font-weight:900; color:#e63946; }
.stat-label { font-size:10px; color:#6b7280; font-weight:500; margin-top:1px; text-transform:uppercase; letter-spacing:0.5px; }

.sidebar-nav { background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.04); animation: fadeUp 0.3s 0.05s ease both; }
.sidebar-nav a {
  display:flex; align-items:center; gap:12px;
  padding:13px 18px; font-size:14px; font-weight:500;
  color:#374151; text-decoration:none;
  border-bottom:1px solid #f3f4f6; transition:all 0.15s;
}
.sidebar-nav a:last-child { border-bottom:none; }
.sidebar-nav a:hover { background:#f8f9fc; color:#0f1117; padding-left:22px; }
.sidebar-nav a.active { color:#e63946; background:#fff0f1; font-weight:600; }
.sidebar-nav a .icon { font-size:16px; width:20px; }

/* MAIN CONTENT */
.main { display:flex; flex-direction:column; gap:20px; }

.card {
  background:#fff; border:1px solid #e5e7eb; border-radius:16px;
  overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.04);
  animation: fadeUp 0.3s 0.1s ease both;
}

.card-head {
  padding:20px 24px; border-bottom:1px solid #f3f4f6;
  display:flex; align-items:center; justify-content:space-between;
}
.card-head h3 { font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px; }
.card-body { padding:24px; }

.success-msg {
  background:#f0fdf4; border:1.5px solid #86efac; color:#16a34a;
  padding:12px 16px; border-radius:10px; font-size:13px;
  font-weight:500; margin-bottom:18px; display:flex; align-items:center; gap:8px;
}
.error-msg {
  background:#fff0f1; border:1.5px solid #fca5a5; color:#e63946;
  padding:12px 16px; border-radius:10px; font-size:13px;
  font-weight:500; margin-bottom:18px; display:flex; align-items:center; gap:8px;
}

.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.form-group input, .form-group textarea, .form-group select {
  width:100%; background:#f8f9fc; border:1.5px solid #e5e7eb;
  border-radius:10px; padding:11px 14px; color:#0f1117;
  font-family:'Inter',sans-serif; font-size:14px; outline:none; transition:all 0.2s;
}
.form-group input:focus, .form-group textarea:focus {
  border-color:#e63946; background:#fff; box-shadow:0 0 0 3px rgba(230,57,70,0.1);
}
.form-group textarea { resize:vertical; min-height:90px; }
.form-group input::placeholder, .form-group textarea::placeholder { color:#c0c4cc; }

.btn { padding:11px 22px; border-radius:10px; font-family:'Inter',sans-serif; font-weight:600; font-size:14px; cursor:pointer; transition:all 0.2s; border:none; }
.btn-primary { background:#e63946; color:#fff; }
.btn-primary:hover { background:#c1121f; transform:translateY(-1px); box-shadow:0 6px 16px rgba(230,57,70,0.25); }
.btn-ghost { background:#f8f9fc; color:#374151; border:1.5px solid #e5e7eb; }
.btn-ghost:hover { border-color:#e63946; color:#e63946; }
.btn-danger { background:#fff0f1; color:#e63946; border:1.5px solid #fca5a5; }
.btn-danger:hover { background:#e63946; color:#fff; border-color:#e63946; }

/* ARTICLE ROW */
.article-row {
  display:flex; align-items:center; gap:14px;
  padding:14px 0; border-bottom:1px solid #f3f4f6;
}
.article-row:last-child { border-bottom:none; padding-bottom:0; }
.article-emoji { font-size:26px; flex-shrink:0; }
.article-info { flex:1; }
.article-title { font-size:14px; font-weight:600; margin-bottom:3px; }
.article-meta { font-size:12px; color:#6b7280; }

/* DANGER ZONE */
.danger-item {
  display:flex; justify-content:space-between; align-items:center;
  padding:16px; background:#f8f9fc; border-radius:12px;
  border:1px solid #e5e7eb; margin-bottom:12px;
}
.danger-item:last-child { margin-bottom:0; }
.danger-item h4 { font-size:14px; font-weight:600; margin-bottom:3px; }
.danger-item p { font-size:12px; color:#6b7280; }
.danger-item.red { border-color:#fca5a5; background:#fff8f8; }

@media (max-width:900px) {
  .page { grid-template-columns:1fr; }
  .form-row { grid-template-columns:1fr; }
}
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo"><div class="logo-dot"></div>Sport<span>Pulse</span></a>
    <div class="nav-right">
      <a href="index.php" class="nav-link">← Начало</a>
      
      <a href="logout.php" class="nav-link danger">Изход</a>
      <div class="nav-user">
        <div class="nav-avatar"><?= $initials ?></div>
        <?= htmlspecialchars($parts[0]) ?>
      </div>
    </div>
  </div>
</nav>

<div class="page">

  <div class="sidebar">
    <div class="profile-card">
      <div class="big-avatar"><?= $initials ?></div>
      <h3><?= htmlspecialchars($uname) ?></h3>
      <p><?= htmlspecialchars($uemail) ?></p>
      <div class="stats-row">
        <div class="stat"><div class="stat-num"><?= $articleCount ?></div><div class="stat-label">Статии</div></div>
        <div class="stat"><div class="stat-num"><?= $savedCount ?></div><div class="stat-label">Запазени</div></div>
        <div class="stat"><div class="stat-num">⭐</div><div class="stat-label">Член</div></div>
      </div>
    </div>

    <div class="sidebar-nav">
      <a href="#" onclick="showTab('activity')" class="active" id="nav-activity"><span class="icon">📊</span> Активност</a>
      <a href="#" onclick="showTab('edit')" id="nav-edit"><span class="icon">✏️</span> Редактирай профил</a>
      <a href="#" onclick="showTab('password')" id="nav-password"><span class="icon">🔒</span> Парола</a>
      <a href="#" onclick="showTab('account')" id="nav-account"><span class="icon">⚠️</span> Акаунт</a>
      <a href="settings.php"><span class="icon">⚙️</span> Настройки</a>
      <a href="notifications.php"><span class="icon">🔔</span> Известия</a>
    </div>
  </div>

  <div class="main">
        
    <div class="card" id="tab-activity">
      <div class="card-head"><h3>📊 Моята активност</h3></div>
      <div class="card-body">
        <?php if (empty($myArticles)): ?>
          <p style="color:#6b7280;font-size:14px;"><b>Искате да споделите своята спортна експертиза? <br>Търсим автори с отношение към спорта, които искат да достигнат до широка аудитория. <br>Пишете ни, за да обсъдим как да станете част от нашия екип. <br> <a href="/sportnews/contact.php" style="color:#e63946;font-weight:600;">Контакти →</a></p>
        <?php else: ?>
          <?php foreach ($myArticles as $a): ?>
          <div class="article-row">
            <div class="article-emoji">
              <?php if (!empty($a['image'])): ?>
                <img src="<?= htmlspecialchars($a['image']) ?>" style="width:48px;height:38px;object-fit:cover;border-radius:6px;">
              <?php else: ?>
                <?= $a['emoji'] ?>
              <?php endif; ?>
              
            </div>
            <div class="article-info">
              <div class="article-title"><?= htmlspecialchars($a['title']) ?></div>
              <div class="article-meta"><?= htmlspecialchars($a['category']) ?><?= !empty($a['league']) ? ' · '.$a['league'] : '' ?> · <?= date($php_profile_date, strtotime($a['created_at'])) ?></div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
              <a href="article.php?id=<?= $a['id'] ?>" class="btn btn-ghost" style="font-size:12px;padding:6px 12px;text-decoration:none;">👁️ View</a>
              <a href="edit_article.php?id=<?= $a['id'] ?>" class="btn btn-primary" style="font-size:12px;padding:6px 12px;text-decoration:none;">✏️ Edit</a>
              <a href="/sportnews/admin/delete_article.php?id=<?= $a['id'] ?>&redirect=home"
                 class="btn btn-danger" style="font-size:12px;padding:6px 12px;text-decoration:none;"
                 onclick="return confirm('Delete this article?')">🗑️</a>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="card" id="tab-edit" style="display:none;">
      <div class="card-head"><h3>✏️ Редактирай профил</h3></div>
      <div class="card-body">
        <?php if ($success && isset($_POST['update_profile'])): ?><div class="success-msg">✅ <?= $success ?></div><?php endif; ?>
        <?php if ($error && isset($_POST['update_profile'])): ?><div class="error-msg">⚠️ <?= $error ?></div><?php endif; ?>
        <form method="POST">
          <input type="hidden" name="update_profile" value="1">
          <div class="form-row">
            <div class="form-group"><label>Име</label><input type="text" name="name" value="<?= htmlspecialchars($uname) ?>" required></div>
            <div class="form-group"><label>Имейл</label><input type="email" name="email" value="<?= htmlspecialchars($uemail) ?>" required></div>
          </div>
          <div class="form-group"><label>Био</label><textarea name="bio" placeholder="Разкажи за себе си..."></textarea></div>
          <div class="form-group"><label>Любим спорт</label><input type="text" name="sport" placeholder="Футбол, Баскетбол..."></div>
          <button type="submit" class="btn btn-primary">Запази промените</button>
        </form>
      </div>
    </div>

    <div class="card" id="tab-password" style="display:none;">
      <div class="card-head"><h3>🔒 Промени паролата</h3></div>
      <div class="card-body">
        <?php if ($success && isset($_POST['change_password'])): ?><div class="success-msg">✅ <?= $success ?></div><?php endif; ?>
        <?php if ($error && isset($_POST['change_password'])): ?><div class="error-msg">⚠️ <?= $error ?></div><?php endif; ?>
        <form method="POST">
          <input type="hidden" name="change_password" value="1">
          <div class="form-group"><label>Сегашна парола</label><input type="password" name="current_password" placeholder="••••••••" required></div>
          <div class="form-row">
            <div class="form-group"><label>Нова парола</label><input type="password" name="new_password" placeholder="Минимум 8 симвула" minlength="8" required></div>
            <div class="form-group"><label>Потвърди паролата</label><input type="password" name="confirm_password" placeholder="Повтори паролата" required></div>
          </div>
          <button type="submit" class="btn btn-primary">Промени паролата</button>
        </form>
      </div>
    </div>

    <div class="card" id="tab-account" style="display:none;">
      <div class="card-head"><h3>⚠️ Настройки на акаунта</h3></div>
      <div class="card-body">
        <div class="danger-item">
          <div><h4>Изход от всички устройства</h4><p>Прекратете всички активни сесии на всяко устройство.</p></div>
          <a href="logout.php" class="btn btn-ghost">Изход от всички</a>
        </div>
        <div class="danger-item red">
          <div><h4 style="color:#e63946;">Изтрий акаунт</h4><p>Изтрийте окончателно профила си и всички данни. Не може да бъде отменено.</p></div>
          <button onclick="confirm('Delete account? This cannot be undone!') && window.location.href='delete_account.php'" class="btn btn-danger">Изтрий</button>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function showTab(name) {
    ['activity','edit','password','account'].forEach(t => {
        document.getElementById('tab-'+t).style.display = 'none';
        document.getElementById('nav-'+t) && document.getElementById('nav-'+t).classList.remove('active');
    });
    document.getElementById('tab-'+name).style.display = 'block';
    document.getElementById('nav-'+name) && document.getElementById('nav-'+name).classList.add('active');
    return false;
}
</script>
</body>
</html>