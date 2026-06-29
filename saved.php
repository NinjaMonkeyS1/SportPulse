<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) { 
    header("Location: login.php"); 
    exit; 
}

$uname = $_SESSION['user']['name'];
$uemail = $_SESSION['user']['email'];
$parts = explode(' ', trim($uname));

// Работа с кирилица за инициалите
$first_letter = mb_substr($parts[0], 0, 1, 'UTF-8');
$last_letter = count($parts) > 1 ? mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') : mb_substr($parts[0], 1, 1, 'UTF-8');
$initials = mb_strtoupper($first_letter . $last_letter, 'UTF-8');
$display = $parts[0] . (count($parts) > 1 ? ' ' . mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') . '.' : '');

/* 1. PHP ЛОГИКА: Взимане на запазените статии от базата данни.
  Правим JOIN между таблицата за запазени статии и основната таблица 'articles'
*/
$stmt = $pdo->prepare("
    SELECT a.* FROM articles a 
    JOIN saved_articles s ON a.id = s.article_id 
    WHERE s.user_email = ? 
    ORDER BY s.created_at DESC
");
$stmt->execute([$uemail]);
$savedArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Запазени статии — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
.page-wrap { max-width:900px; margin:40px auto; padding:0 24px; min-height: 60vh; }
.page-title { font-size:32px; font-weight:900; color:var(--text-main); letter-spacing:-1px; margin-bottom:6px; }
.page-sub { color:#6b7280; font-size:14px; margin-bottom:32px; }

/* РЕШЕТКА ЗА СТАТИИТЕ */
.saved-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 20px;
}
.article-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: all 0.25s;
    display: flex;
    flex-direction: column;
}
.article-card:hover {
    transform: translateY(-4px);
    border-color: #e63946;
    box-shadow: 0 12px 30px rgba(0,0,0,0.06);
}
.article-thumb {
    height: 160px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    overflow: hidden;
}
.article-thumb img { width: 100%; height: 100%; object-fit: cover; }
.article-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }
.article-cat { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #e63946; margin-bottom: 8px; }
.article-card-title { font-size: 16px; font-weight: 800; line-height: 1.4; color: #0f1117; margin-bottom: 8px; }
.article-excerpt { font-size: 13px; color: #6b7280; line-height: 1.5; margin-bottom: 14px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

/* ПРЕМАХВАНЕ ОТ ЗАПАЗЕНИ */
.btn-remove {
    margin-top: auto;
    background: none;
    border: none;
    color: #ef4444;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    text-align: left;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-remove:hover { text-decoration: underline; }

/* EMPTY STATE */
.empty-state {
    background:#fff; border:1.5px solid #e5e7eb; border-radius:16px;
    padding:60px 40px; text-align:center; box-shadow: 0 4px 20px rgba(0,0,0,0.02);
}
.empty-state .icon { font-size:64px; margin-bottom:20px; display: inline-block; animation: float 3s ease-in-out infinite; }
.empty-state h3 { font-size:22px; font-weight:800; margin-bottom:8px; color:#0f1117; }
.empty-state p { color:#6b7280; font-size:14px; margin-bottom:24px; max-width: 400px; margin-left: auto; margin-right: auto; line-height: 1.5; }

.btn-browse {
    background: #e63946; color: #fff; border: none; padding: 12px 30px; border-radius: 10px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 14px; text-decoration: none; display: inline-block; transition: all 0.2s;
}
.btn-browse:hover { background: #c1121f; transform: translateY(-1px); }

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo"><div class="logo-dot"></div>Sport<span>Pulse</span></a>
     <ul class="nav-links">
      <li><a href="index.php">Начало</a></li>
      
      <li class="nav-dropdown-wrap">
        <a href="league.php?category=Football">Футбол</a>
        <div class="nav-mega">
          <a href="league.php?category=Football">Всичко за Футбол</a>
          <a href="league.php?category=Football&league=Premier League">Висша лига</a>
          <a href="league.php?category=Football&league=Champions League">Шампионска лига</a>
          <a href="league.php?category=Football&league=La Liga">Ла Лига</a>
        </div>
      </li>
      
      <li class="nav-dropdown-wrap">
        <a href="league.php?category=Basketball">Баскетбол</a>
        <div class="nav-mega">
          <a href="league.php?category=Basketball">Всичко за Баскетбол</a>
          <a href="league.php?category=Basketball&league=NBA">NBA</a>
          <a href="league.php?category=Basketball&league=EuroLeague">Евролига</a>
        </div>
      </li>

      <li class="nav-dropdown-wrap">
        <a href="league.php?category=Tennis">Тенис</a>
        <div class="nav-mega">
          <a href="league.php?category=Tennis">Всичко за Тенис</a>
          <a href="league.php?category=Tennis&league=ATP Tour">ATP Тур</a>
          <a href="league.php?category=Tennis&league=Grand Slam">Голям шлем</a>
        </div>
      </li>

      <li class="nav-dropdown-wrap">
        <a href="#" onclick="return false;">Още ▾</a>
        <div class="nav-mega">
          <a href="league.php?category=Formula 1">🏎️ Формула 1</a>
          <a href="league.php?category=Rugby">🏉 Ръгби</a>
          <a href="league.php?category=Olympics">🏅 Олимпийски игри</a>
          <a href="league.php?category=Volleyball">🏐 Волейбол</a>
          <a href="league.php?category=Boxing">🥊 Бокс & MMA</a>
          <a href="league.php?category=Golf">⛳ Голф</a>
          <a href="league.php?category=Cricket">🏏 Крикет</a>
          <a href="league.php?category=Swimming">🏊 Плуване</a>
          <a href="league.php?category=Esports">🎮 eSports</a>
        </div>
      </li>
    </ul>
    
    <div class="nav-right">
      <div class="profile-avatar" id="profileAvatar">
        <div class="avatar-circle"><?= $initials ?></div>
        <span class="avatar-name"><?= htmlspecialchars($display) ?></span>
        <span style="color:var(--muted);font-size:12px;">▾</span>

        <div class="profile-menu" id="profileMenu">
          <div class="profile-menu-header">
            <div class="big-avatar"><?= $initials ?></div>
            <div>
              <div class="profile-menu-name"><?= htmlspecialchars($uname) ?></div>
              <div class="profile-menu-email"><?= htmlspecialchars($uemail) ?></div>
            </div>
          </div>
          <ul>
            <li><a href="profile.php">👤 Моят профил</a></li>
            <li><a href="saved.php" class="active">⭐ Запазени статии</a></li>
            <li><a href="notifications.php">🔔 Известия</a></li>
            <li><a href="settings.php">⚙️ Настройки</a></li>
            <li><a href="/sportnews/admin/add_article.php">📝 Писане на статия</a></li>
            <div class="divider-line"></div>
            <li class="danger"><a href="logout.php">🚪 Изход</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="page-wrap">
    <div class="page-title">⭐ Запазени статии</div>
    <div class="page-sub">Статии, които сте маркирали като интересни</div>
    
    <?php if (empty($savedArticles)): ?>
        <div class="empty-state">
            <div class="icon">🔖</div>
            <h3>Все още нямате запазени статии</h3>
            <p>Когато намерите интересна новина, можете да я запазите от бутона „Запази“ вътре в нея и тя ще се появи тук.</p>
            <a href="index.php" class="btn-browse">Разгледай новините</a>
        </div>
    <?php else: ?>
        <div class="saved-grid">
            <?php foreach ($savedArticles as $art): ?>
                <div class="article-card-wrapper" style="position: relative; display: flex; flex-direction: column;">
                    <a href="article.php?id=<?= $art['id'] ?>" class="article-card">
                        <div class="article-thumb">
                            <?php if (!empty($art['image'])): ?>
                                <img src="<?= htmlspecialchars($art['image']) ?>" alt="">
                            <?php else: ?>
                                <?= htmlspecialchars($art['emoji'] ?? '📰') ?>
                            <?php endif; ?>
                        </div>
                        <div class="article-body">
                            <div class="article-cat"><?= htmlspecialchars($art['category']) ?></div>
                            <div class="article-card-title"><?= htmlspecialchars($art['title']) ?></div>
                            <div class="article-excerpt"><?= htmlspecialchars($art['excerpt'] ?? '') ?></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer>
  <strong>⚡ SportPulse</strong><br><br>
  <a href="index.php">Начало</a> <a href="profile.php">Профил</a> <a href="logout.php">Изход</a><br><br>
  <span style="font-size:12px;">© <?= date('Y') ?> SportPulse. Всички права запазени.</span>
</footer>

<script>
const profileAvatar = document.getElementById('profileAvatar');
const profileMenu   = document.getElementById('profileMenu');

if (profileAvatar && profileMenu) {
    profileAvatar.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.classList.toggle('open');
    });
    profileMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}
document.addEventListener('click', function() {
    if (profileMenu && profileMenu.classList.contains('open')) {
        profileMenu.classList.remove('open');
    }
});
</script>
</body>
</html>