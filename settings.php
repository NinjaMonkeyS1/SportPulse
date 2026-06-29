<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$uname   = $_SESSION['user']['name'];
$uemail  = $_SESSION['user']['email'];
$parts   = explode(' ', trim($uname));

// Поправка за кирилица при инициалите
$first_letter = mb_substr($parts[0], 0, 1, 'UTF-8');
$last_letter = count($parts) > 1 ? mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') : mb_substr($parts[0], 1, 1, 'UTF-8');
$initials = mb_strtoupper($first_letter . $last_letter, 'UTF-8');

$success = "";

// 1. ЗАПАЗВАНЕ НА НАСТРОЙКИТЕ ПРИ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_notif = isset($_POST['email_notif']) ? 1 : 0;
    $breaking    = isset($_POST['breaking']) ? 1 : 0;
    $scores      = isset($_POST['scores']) ? 1 : 0;
    $digest      = isset($_POST['digest']) ? 1 : 0;
    $dateformat  = $_POST['dateformat'] ?? 'DD/MM/YYYY';
    $analytics   = isset($_POST['analytics']) ? 1 : 0;
    $ads         = isset($_POST['ads']) ? 1 : 0;
    $public      = isset($_POST['public']) ? 1 : 0;
    
    // Спортовете идват като масив, съединяваме ги със запетая (напр: "Футбол,Баскетбол")
    $fav_sports  = isset($_POST['fav_sports']) ? implode(',', $_POST['fav_sports']) : '';

    // Използваме INSERT ... ON DUPLICATE KEY UPDATE, за да създадем или обновим запис
    $stmt = $pdo->prepare("
        INSERT INTO user_settings (user_email, email_notif, breaking, scores, digest, fav_sports, dateformat, analytics, ads, public)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            email_notif = VALUES(email_notif),
            breaking = VALUES(breaking),
            scores = VALUES(scores),
            digest = VALUES(digest),
            fav_sports = VALUES(fav_sports),
            dateformat = VALUES(dateformat),
            analytics = VALUES(analytics),
            ads = VALUES(ads),
            public = VALUES(public)
    ");
    
    $stmt->execute([$uemail, $email_notif, $breaking, $scores, $digest, $fav_sports, $dateformat, $analytics, $ads, $public]);
    $success = "Настройките бяха запазени успешно!";
}

// 2. ЗАРЕЖДАНЕ НА ТЕКУЩИТЕ НАСТРОЙКИ ОТ БАЗАТА ДАННИ
$stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_email = ?");
$stmt->execute([$uemail]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Ако потребителят няма записи още, слагаме стойности по подразбиране
if (!$settings) {
    $settings = [
        'email_notif' => 1, 'breaking' => 1, 'scores' => 0, 'digest' => 1,
        'fav_sports' => 'Футбол,Баскетбол', 'dateformat' => 'DD/MM/YYYY',
        'analytics' => 1, 'ads' => 0, 'public' => 1
    ];
}

// Правим масив от любимите спортове за по-лесна проверка в HTML-а
$user_sports = explode(',', $settings['fav_sports']);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Настройки — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#f8f9fc; color:#0f1117; min-height:100vh; }
::selection { background:#e63946; color:#fff; }

nav {
  background:#fff; border-bottom:1px solid #e5e7eb;
  position:sticky; top:0; z-index:100;
}
.nav-inner {
  max-width:1200px; margin:0 auto;
  display:flex; align-items:center;
  padding:0 24px; height:60px; gap:16px;
}
.logo { font-size:20px; font-weight:900; text-decoration:none; color:#0f1117; display:flex; align-items:center; gap:8px; }
.logo span { color:#e63946; }
.logo-dot { width:8px; height:8px; background:#e63946; border-radius:50%; animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.5;transform:scale(0.7);} }
.nav-right { margin-left:auto; display:flex; align-items:center; gap:10px; }
.nav-avatar { width:32px; height:32px; border-radius:50%; background:#e63946; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.nav-user { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:600; }
.nav-link { font-size:13px; font-weight:500; color:#6b7280; text-decoration:none; padding:6px 12px; border-radius:8px; transition:all 0.15s; }
.nav-link:hover { background:#f0f2f7; color:#0f1117; }
.nav-link.danger { color:#e63946; }
.nav-link.danger:hover { background:#fff0f1; }

.page { max-width:1100px; margin:0 auto; padding:32px 24px; display:grid; grid-template-columns:240px 1fr; gap:24px; align-items:start; }

/* SIDEBAR */
.sidebar-nav {
  background:#fff; border:1px solid #e5e7eb; border-radius:16px;
  overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.04);
  animation: fadeUp 0.3s ease;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);} }

.sidebar-title {
  padding:14px 18px; font-size:11px; font-weight:700;
  letter-spacing:1px; text-transform:uppercase; color:#6b7280;
  border-bottom:1px solid #f3f4f6; background:#f8f9fc;
}

.sidebar-nav a {
  display:flex; align-items:center; gap:12px;
  padding:13px 18px; font-size:14px; font-weight:500;
  color:#374151; text-decoration:none;
  border-bottom:1px solid #f3f4f6; transition:all 0.15s;
}
.sidebar-nav a:last-child { border-bottom:none; }
.sidebar-nav a:hover { background:#f8f9fc; padding-left:22px; }
.sidebar-nav a.active { color:#e63946; background:#fff0f1; font-weight:600; }
.sidebar-nav a .icon { font-size:15px; width:20px; }

/* MAIN */
.main { display:flex; flex-direction:column; gap:20px; }

.card {
  background:#fff; border:1px solid #e5e7eb; border-radius:16px;
  overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.04);
  animation: fadeUp 0.3s ease both;
}
.card:nth-child(2) { animation-delay:0.05s; }
.card:nth-child(3) { animation-delay:0.1s; }
.card:nth-child(4) { animation-delay:0.15s; }

.card-head {
  padding:18px 24px; border-bottom:1px solid #f3f4f6;
  display:flex; align-items:center; gap:10px;
}
.card-head h3 { font-size:15px; font-weight:700; }
.card-head p { font-size:13px; color:#6b7280; margin-top:2px; }

/* TOGGLE ROWS */
.toggle-row {
  display:flex; justify-content:space-between; align-items:center;
  padding:16px 24px; border-bottom:1px solid #f3f4f6; transition:background 0.15s;
}
.toggle-row:last-child { border-bottom:none; }
.toggle-row:hover { background:#fafafa; }

.toggle-info h4 { font-size:14px; font-weight:600; margin-bottom:2px; }
.toggle-info p { font-size:12px; color:#6b7280; }

.toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider {
  position:absolute; inset:0; background:#e5e7eb;
  border-radius:24px; cursor:pointer; transition:0.25s;
}
.toggle-slider::before {
  content:''; position:absolute; width:18px; height:18px;
  left:3px; bottom:3px; background:#fff; border-radius:50%;
  transition:0.25s; box-shadow:0 1px 4px rgba(0,0,0,0.15);
}
.toggle-switch input:checked + .toggle-slider { background:#e63946; }
.toggle-switch input:checked + .toggle-slider::before { transform:translateX(20px); }

/* SPORT CHIPS */
.sports-grid { display:flex; flex-wrap:wrap; gap:10px; padding:20px 24px; }
.sport-chip {
  display:flex; align-items:center; gap:8px;
  padding:8px 16px; border-radius:20px;
  border:1.5px solid #e5e7eb; background:#fff;
  font-size:13px; font-weight:600; cursor:pointer; transition:all 0.15s;
  user-select:none;
}
.sport-chip input { display:none; } /* Скриваме оригиналния чекбокс */
.sport-chip:hover { border-color:#e63946; }

/* Модерна CSS селекция: Когато вграденият чекбокс е избран, оцвети целия чип */
.sport-chip:has(input:checked) {
  border-color:#e63946;
  background:#fff0f1;
  color:#e63946;
}

/* SELECT */
.select-row { padding:16px 24px; border-bottom:1px solid #f3f4f6; }
.select-row:last-child { border-bottom:none; }
.select-row label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:8px; }
.select-row select {
  width:100%; background:#f8f9fc; border:1.5px solid #e5e7eb;
  border-radius:10px; padding:10px 14px; color:#0f1117;
  font-family:'Inter',sans-serif; font-size:14px; outline:none; transition:border-color 0.2s;
}
.select-row select:focus { border-color:#e63946; }

/* SAVE BUTTON BAR */
.save-bar {
  background:#fff; border:1px solid #e5e7eb; border-radius:16px;
  padding:18px 24px; display:flex; justify-content:flex-end; align-items:center; gap:12px;
  box-shadow:0 2px 12px rgba(0,0,0,0.04); animation: fadeUp 0.3s 0.2s ease both;
  margin-top: 10px;
}
.btn-save {
  background:#e63946; color:#fff; border:none;
  padding:11px 28px; border-radius:10px;
  font-family:'Inter',sans-serif; font-weight:700; font-size:14px;
  cursor:pointer; transition:all 0.2s;
}
.btn-save:hover { background:#c1121f; transform:translateY(-1px); box-shadow:0 6px 16px rgba(230,57,70,0.25); }
.btn-cancel { background:#f8f9fc; color:#6b7280; border:1.5px solid #e5e7eb; padding:11px 22px; border-radius:10px; font-family:'Inter',sans-serif; font-weight:600; font-size:14px; text-decoration:none; transition:all 0.2s; }
.btn-cancel:hover { border-color:#e63946; color:#e63946; }

.success-msg {
  background:#f0fdf4; border:1.5px solid #86efac; color:#16a34a;
  padding:12px 18px; border-radius:10px; font-size:13px; font-weight:500;
  margin-bottom:20px; display:flex; align-items:center; gap:8px;
  animation: fadeUp 0.3s ease;
}

@media (max-width:900px) {
  .page { grid-template-columns:1fr; }
}
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo"><div class="logo-dot"></div>Sport<span>Pulse</span></a>
    <div class="nav-right">
      <a href="index.php" class="nav-link">← Начало</a>
      <a href="profile.php" class="nav-link">Профил</a>
      <a href="logout.php" class="nav-link danger">Изход</a>
      <div class="nav-user">
        <div class="nav-avatar"><?= $initials ?></div>
        <?= htmlspecialchars($parts[0]) ?>
      </div>
    </div>
  </div>
</nav>

<div class="page">

  <!-- SIDEBAR -->
  <div class="sidebar-nav">
    <div class="sidebar-title">Акаунт</div>
    <a href="profile.php"><span class="icon">👤</span> Моят профил</a>
    <a href="notifications.php"><span class="icon">🔔</span> Известия</a>
    <a href="settings.php" class="active"><span class="icon">⚙️</span> Настройки</a>
    <a href="saved.php"><span class="icon">⭐</span> Запазени статии</a>
    <a href="logout.php" style="color:#e63946;"><span class="icon">🚪</span> Изход</a>
  </div>

  <!-- MAIN (ФОРМАТА Е ОТВОРЕНА ТУК) -->
  <form method="POST" action="">
  <div class="main">

    <?php if ($success): ?>
    <div class="success-msg">✅ <?= $success ?></div>
    <?php endif; ?>

    <!-- NOTIFICATIONS -->
    <div class="card">
      <div class="card-head">
        <div>
          <h3>🔔 Известия</h3>
          <p>Избери за какво искаш да си информиран</p>
        </div>
      </div>
      <div class="card-body">
        <div class="toggle-row">
          <div class="toggle-info"><h4>Известия по имейл</h4><p>Получавайте актуализации и новини по имейл</p></div>
          <label class="toggle-switch"><input type="checkbox" name="email_notif" <?= $settings['email_notif'] ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
        </div>
        <div class="toggle-row">
          <div class="toggle-info"><h4>Сигнали за последни новини</h4><p> Получавайте незабавни известия за важни истории</p></div>
          <label class="toggle-switch"><input type="checkbox" name="breaking" <?= $settings['breaking'] ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
        </div>
        <div class="toggle-row">
          <div class="toggle-info"><h4>Сигнали за резултати на живо</h4><p>Голове, резултати и актуална информация на живо</p></div>
          <label class="toggle-switch"><input type="checkbox" name="scores" <?= $settings['scores'] ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
        </div>
        <div class="toggle-row">
          <div class="toggle-info"><h4>Седмичен сборник</h4><p>Обобщение на най-важните новини всеки понеделник</p></div>
          <label class="toggle-switch"><input type="checkbox" name="digest" <?= $settings['digest'] ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
        </div>
      </div>
    </div>

    <!-- FAVOURITE SPORTS -->
    <div class="card">
      <div class="card-head">
        <div><h3>🏆 Любими спортове</h3><p>Персонализирайте вашата емисия новини</p></div>
      </div>
      <div class="card-body">
        <div class="sports-grid">
          <?php
          $all_sports = [
              'Футбол' => '⚽ Футбол', 'Баскетбол' => '🏀 Баскетбол', 'Ф1' => '🏎️ Ф1', 
              'Тенис' => '🎾 Тенис', 'Ръгби' => '🏉 Ръгби', 'Голф' => '⛳ Голф', 
              'Бокс' => '🥊 Бокс', 'Плуване' => '🏊 Плуване', 'Крикет' => '🏏 Крикет', 
              'Олимпийски игри' => '🏋️ Олимпийски игри'
          ];
          
          foreach ($all_sports as $key => $label): 
              $is_selected = in_array($key, $user_sports);
          ?>
            <!-- Премахнат е onclick атрибута, всичко вече се управлява от CSS -->
            <label class="sport-chip">
                <input type="checkbox" name="fav_sports[]" value="<?= $key ?>" <?= $is_selected ? 'checked' : '' ?>>
                <?= $label ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- DISPLAY -->
    <div class="card">
      <div class="card-head"><div><h3>🌍 Дисплей и регион</h3><p>Предпочитания</p></div></div>
      <div class="card-body">
        <div class="select-row">
          <label>Формат на датата</label>
          <select name="dateformat">
            <option value="DD/MM/YYYY" <?= $settings['dateformat'] === 'DD/MM/YYYY' ? 'selected' : '' ?>>DD/MM/YYYY</option>
            <option value="MM/DD/YYYY" <?= $settings['dateformat'] === 'MM/DD/YYYY' ? 'selected' : '' ?>>MM/DD/YYYY</option>
            <option value="YYYY-MM-DD" <?= $settings['dateformat'] === 'YYYY-MM-DD' ? 'selected' : '' ?>>YYYY-MM-DD</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ДОБАВЕНА Е ЛИПСВАЩАТА ЛЕНТА С БУТОНИ ЗА ЗАПАЗВАНЕ -->
    <div class="save-bar">
        <a href="index.php" class="btn-cancel">Отказ</a>
        <button type="submit" class="btn-save">Запази промените</button>
    </div>

  </div> <!-- Край на .main -->
  </form> <!-- ФОРМАТА Е ПРАВИЛНО ЗАТВОРЕНА ТУК -->

</div> <!-- Край на .page -->

</body>
</html>