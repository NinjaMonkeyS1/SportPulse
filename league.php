<?php
session_start();
require 'db.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$league   = isset($_GET['league'])   ? trim($_GET['league'])   : '';

if (!$category) { header("Location: index.php"); exit; }

$isLoggedIn = isset($_SESSION['user']);
if ($isLoggedIn) {
    $uname    = $_SESSION['user']['name'];
    $uemail   = $_SESSION['user']['email'];
    $parts    = explode(' ', trim($uname));
    
    // ПОПРАВКА: Използваме mb_substr за правилна работа с кирилица
    $first_letter = mb_substr($parts[0], 0, 1, 'UTF-8');
    $last_letter = count($parts) > 1 ? mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') : mb_substr($parts[0], 1, 1, 'UTF-8');
    $initials = mb_strtoupper($first_letter . $last_letter, 'UTF-8');
    
    $display  = $parts[0] . (count($parts) > 1 ? ' ' . mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') . '.' : '');
}

// Изграждане на заявката
if ($league) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE category = ? AND league = ? ORDER BY created_at DESC");
    $stmt->execute([$category, $league]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE category = ? ORDER BY created_at DESC");
    $stmt->execute([$category]);
}
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Взимане на съществуващите лиги за тази категория
$stmt = $pdo->prepare("SELECT DISTINCT league FROM articles WHERE category = ? AND league IS NOT NULL AND league != '' ORDER BY league");
$stmt->execute([$category]);
$existingLeagues = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Преведени спортни лиги и категории на български
$translateMap = [
    'Football' => 'Футбол', 'Basketball' => 'Баскетбол', 'Tennis' => 'Тенис', 
    'Formula 1' => 'Формула 1', 'Rugby' => 'Ръгби', 'Golf' => 'Голф', 
    'Boxing' => 'Бокс', 'Olympics' => 'Олимпийски игри', 'Cricket' => 'Крикет', 'Swimming' => 'Плуване',
    'Premier League' => 'Висша лига', 'La Liga' => 'Ла Лига', 'Serie A' => 'Серия А', 
    'Bundesliga' => 'Бундеслига', 'Ligue 1' => 'Лига 1', 'Champions League' => 'Шампионска лига', 
    'World Cup' => 'Световно първеснтво', 'NBA' => 'NBA', 'EuroLeague' => 'Евролига', 'NCAA' => 'NCAA',
    'ATP Tour' => 'ATP Тур', 'WTA Tour' => 'WTA Тур', 'Grand Slam' => 'Голям шлем',
    'F1 World Championship' => 'Световен шампионат Ф1', 'F2' => 'F2', 'F3' => 'F3',
    'Six Nations' => 'Six Nations', 'Premiership' => 'Премиършип',
    'PGA Tour' => 'PGA Тур', 'European Tour' => 'Европейски тур', 'Majors' => 'Мейджърс',
    'Heavyweight' => 'Тежка категория', 'Middleweight' => 'Средна категория', 'Lightweight' => 'Лека категория',
    'Summer Olympics' => 'Летни олимпийски игри', 'Winter Olympics' => 'Зимни олимпийски игри',
    'Test Cricket' => 'Тест крикет', 'ODI' => 'ODI', 'T20' => 'T20',
    'World Aquatics' => 'Световни водни спортове'
];

$bgCategory = $translateMap[$category] ?? $category;
$bgLeague = $translateMap[$league] ?? $league;

$leaguesBySport = [
    'Football'    => ['Premier League','La Liga','Serie A','Bundesliga','Ligue 1','Champions League','World Cup'],
    'Basketball'  => ['NBA','EuroLeague','NCAA'],
    'Tennis'      => ['ATP Tour','WTA Tour','Grand Slam'],
    'Formula 1'   => ['F1 World Championship','F2','F3'],
    'Rugby'       => ['Six Nations','World Cup','Premiership'],
    'Golf'        => ['PGA Tour','European Tour','Majors'],
    'Boxing'      => ['Heavyweight','Middleweight','Lightweight'],
    'Olympics'    => ['Summer Olympics','Winter Olympics'],
    'Cricket'     => ['Test Cricket','ODI','T20'],
    'Swimming'    => ['World Aquatics','Olympics'],
];
$availableLeagues = $leaguesBySport[$category] ?? $existingLeagues;

$categoryEmojis = [
    'Football'=>'⚽','Basketball'=>'🏀','Tennis'=>'🎾','Formula 1'=>'🏎️',
    'Rugby'=>'🏉','Golf'=>'⛳','Boxing'=>'🥊','Olympics'=>'🏋️','Cricket'=>'🏏','Swimming'=>'🏊'
];
$catEmoji = $categoryEmojis[$category] ?? '🏆';
?>

<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($bgLeague ?: $bgCategory) ?> — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
.league-hero {
  background: linear-gradient(135deg, #0f1117 0%, #1a1d27 100%);
  padding: 48px 24px;
  position: relative;
  overflow: hidden;
}
.league-hero::before {
  content: '';
  position: absolute; top:-100px; right:-100px;
  width:300px; height:300px;
  background: radial-gradient(circle, rgba(230,57,70,0.15) 0%, transparent 70%);
}
.league-hero-inner { max-width:1280px; margin:0 auto; position:relative; z-index:1; }
.league-breadcrumb { font-size:13px; color:rgba(255,255,255,0.5); margin-bottom:14px; }
.league-breadcrumb a { color:rgba(255,255,255,0.5); text-decoration:none; }
.league-breadcrumb a:hover { color:#fff; }
.league-title-row { display:flex; align-items:center; gap:16px; }
.league-emoji-big { font-size:48px; }
.league-title { font-size:38px; font-weight:900; color:#fff; letter-spacing:-1px; }
.league-sub { font-size:14px; color:rgba(255,255,255,0.5); margin-top:4px; }

.league-filters {
  max-width:1280px; margin:0 auto; padding:24px;
  display:flex; gap:8px; flex-wrap:wrap;
}
.league-chip {
  padding:8px 18px; border-radius:24px; font-size:13px; font-weight:600;
  background:#fff; border:1.5px solid #e5e7eb; color:#374151;
  text-decoration:none; transition:all 0.15s;
}
.league-chip:hover { border-color:#e63946; color:#e63946; }
.league-chip.active { background:#e63946; border-color:#e63946; color:#fff; }

.league-content { max-width:1280px; margin:0 auto; padding:0 24px 48px; }
.results-info { font-size:13px; color:#6b7280; margin-bottom:20px; }
.results-info strong { color:#0f1117; }

.empty-state {
  background:#fff; border:1.5px solid #e5e7eb; border-radius:16px;
  padding:60px 40px; text-align:center;
}
.empty-state .icon { font-size:52px; margin-bottom:16px; }
.empty-state h3 { font-size:19px; font-weight:800; margin-bottom:6px; }
.empty-state p { color:#6b7280; font-size:14px; }
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
      <div class="search-box">
        <span class="search-icon">⌕</span>
        <input type="text" placeholder="Търсене..">
      </div>

      <?php if ($isLoggedIn): ?>
        <div class="profile-avatar" id="profileAvatar">
          <div class="avatar-circle"><?= $initials ?></div>
          <span class="avatar-name"><?= htmlspecialchars($display) ?></span>
          
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
              <li><a href="saved.php">⭐ Запазени статии</a></li>
              <li><a href="settings.php">⚙️ Настройки</a></li>
              <li><a href="/sportnews/admin/add_article.php">📝 Писане на статия</a></li>
              <li class="danger"><a href="logout.php">🚪 Изход</a></li>
            </ul>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php"><button class="btn-login">Вход</button></a>
        <a href="register.php"><button class="btn-login btn-outline">Регистриране</button></a>
      <?php endif; ?>
    </div> 
  </div> 
</nav>

<div class="league-hero">
  <div class="league-hero-inner">
    <div class="league-breadcrumb">
      <a href="index.php">Начало</a> / <a href="league.php?category=<?= urlencode($category) ?>"><?= htmlspecialchars($bgCategory) ?></a>
      <?php if ($league): ?> / <?= htmlspecialchars($bgLeague) ?><?php endif; ?>
    </div>
    <div class="league-title-row">
      <span class="league-emoji-big"><?= $catEmoji ?></span>
      <div>
        <div class="league-title"><?= htmlspecialchars($bgLeague ?: $bgCategory) ?></div>
        <div class="league-sub">Намерени: <?= count($articles) ?> стати<?= count($articles) !== 1 ? 'и' : 'я' ?></div>
      </div>
    </div>
  </div>
</div>

<div class="league-filters">
  <a href="league.php?category=<?= urlencode($category) ?>" class="league-chip <?= !$league ? 'active' : '' ?>">Всичко за <?= htmlspecialchars($bgCategory) ?></a>
  <?php foreach ($availableLeagues as $l): ?>
    <?php $bgLName = $translateMap[$l] ?? $l; ?>
    <a href="league.php?category=<?= urlencode($category) ?>&league=<?= urlencode($l) ?>" class="league-chip <?= $league === $l ? 'active' : '' ?>"><?= htmlspecialchars($bgLName) ?></a>
  <?php endforeach; ?>
</div>

<div class="league-content">

  <?php if (empty($articles)): ?>
    <div class="empty-state">
      <div class="icon">📭</div>
      <h3>Все още няма статии</h3>
      <p>Няма публикувани новини за <?= htmlspecialchars($bgLeague ?: $bgCategory) ?>. Проверете отново по-късно!</p>
    </div>
  <?php else: ?>
    <div class="results-info">Показване на <strong><?= count($articles) ?></strong> стати<?= count($articles) !== 1 ? 'и' : 'я' ?> <?php if ($league): ?>в <strong><?= htmlspecialchars($bgLeague) ?></strong><?php endif; ?></div>
    <div class="card-grid">
      <?php foreach ($articles as $a): ?>
      <?php $bgArticleLeague = $translateMap[$a['league']] ?? ($translateMap[$a['category']] ?? $a['category']); ?>
      <a href="article.php?id=<?= $a['id'] ?>" class="news-card" style="text-decoration:none;color:inherit;">
        <div class="card-thumb">
          <?php if (!empty($a['image'])): ?>
            <img src="<?= htmlspecialchars($a['image']) ?>" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <?= $a['emoji'] ?>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="card-tag"><?= htmlspecialchars($bgArticleLeague) ?></div>
          <div class="card-title"><?= htmlspecialchars($a['title']) ?></div>
          <div class="card-excerpt"><?= htmlspecialchars($a['excerpt']) ?></div>
          <div class="card-footer">
            <div class="card-author">
              <div class="card-author-dot"><?= mb_strtoupper(mb_substr($a['author'],0,2,'UTF-8'),'UTF-8') ?></div>
              <?= htmlspecialchars($a['author']) ?>
            </div>
            <span><?= date('d.m', strtotime($a['created_at'])) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<footer>
  <strong>⚡ SportPulse</strong>
  Твоят източник на актуални спортни новини, резултати и анализи.<br><br>
  <a href="index.php">Начало</a>
  <a href="#">За нас</a>
  <a href="privacy_policy.php">Политика за поверителност</a>
  <a href="#">Контакти</a>
  <br><br>
  <span style="font-size:12px;">© <?= date('Y') ?> SportPulse. Всички права запазени.</span>
</footer>

<div class="toast" id="toast"></div>

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