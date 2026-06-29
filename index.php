<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

if (!function_exists('t')) {
    function t($key) {
        $translations = [
            'my_profile' => 'Моят профил',
            'notifications' => 'Известия',
            'saved_articles' => 'Запазени статии',
            'settings' => 'Настройки',
            'admin_panel' => 'Админ панел',
            'sign_out' => 'Изход',
            'welcome_title' => 'Добре дошли!'
        ];
        return isset($translations[$key]) ? $translations[$key] : $key;
    }
}

$stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hero = !empty($articles) ? array_shift($articles) : null;

$isLoggedIn = isset($_SESSION['user']);
if ($isLoggedIn) {
    $uname    = $_SESSION['user']['name'];
    $uemail   = $_SESSION['user']['email'];
    $parts    = explode(' ', trim($uname));
    
    $first_letter = mb_substr($parts[0], 0, 1, 'UTF-8');
    $last_letter = count($parts) > 1 ? mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') : mb_substr($parts[0], 1, 1, 'UTF-8');
    $initials = mb_strtoupper($first_letter . $last_letter, 'UTF-8');
    
    $display  = $parts[0] . (count($parts) > 1 ? ' ' . mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') . '.' : '');
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body>

<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo"><div class="logo-dot"></div>Sport<span>Pulse</span></a>
    
    <ul class="nav-links">
      <li><a href="index.php">Начало</a></li>
      
      <!-- Основните спортове в навигацията -->
      <li><a href="league.php?category=Football">Футбол</a></li>
      <li><a href="league.php?category=Basketball">Баскетбол</a></li>
      <li><a href="league.php?category=Tennis">Тенис</a></li>
      <li><a href="league.php?category=Formula 1">Формула 1</a></li>
      <li><a href="league.php?category=Rugby">Ръгби</a></li>
      
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
  
    <!-- Десен панел -->
    <div class="nav-right">
      <?php if (isset($_SESSION['user'])): ?>
        <!-- Профил меню -->
        <div class="profile-avatar" id="profileAvatar">
          <div class="avatar-circle"><?= $initials ?? '??' ?></div>
          <span class="avatar-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
          
         <div class="profile-menu" id="profileMenu">
    <div class="profile-menu-header">
        <div class="profile-menu-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
        <div class="profile-menu-email"><?= htmlspecialchars($_SESSION['user']['email']) ?></div>
    </div>
    <ul>
        <li><a href="profile.php">Моят профил</a></li>
        <li><a href="saved.php">Запазени статии</a></li>
        <li><a href="settings.php">Настройки</a></li>

        <!-- ДОБАВЕН ЛИНК ЗА АДМИНИ И АВТОРИ -->
        <!-- Замени досегашния PHP блок за админ панела с този: -->
<?php
// Проверка в реално време към базата данни
$stmt_check = $pdo->prepare("SELECT role FROM users WHERE email = ?");
$stmt_check->execute([$_SESSION['user']['email']]);
$user_role = $stmt_check->fetchColumn();

if ($user_role === 'admin' || $user_role === 'author'): ?>
    <li style="border-top:1px solid #eee; margin-top:5px; padding-top:5px;">
        <a href="admin/add_article.php" style="color:#e63946; font-weight:bold;">📝 Писане на статия</a>
    </li>
<?php endif; ?>
        
        <li class="danger"><a href="logout.php">Изход</a></li>
    </ul>
</div>
      <?php else: ?>
        <!-- Гост меню -->
        <a href="login.php" class="btn-login">Вход</a>
        <a href="register.php" class="btn-login btn-outline">Регистриране</a>
      <?php endif; ?>
    </div>
  </div> 
</nav>

    <div class="ticker">
  <div class="ticker-inner">
    <span class="ticker-item">Световно първенство по футбол 2026: Груповата фаза приключи, предстоят елиминациите</span>
    <span class="ticker-item">Уимбълдън 2026: Джокович и Алкарас започват похода си към титлата</span>
    <span class="ticker-item">Олимпийски игри 2028: Лос Анджелис разкрива финални детайли по организацията</span>
    <span class="ticker-item">НБА Драфт 2026: Новите таланти вече подписаха с отборите си</span>
    <span class="ticker-item">Ф1 Гран При на Австрия: Отборите се готвят за състезанието на „Ред Бул Ринг“</span>
    <span class="ticker-item">Трансферен пазар: Големите европейски клубове активират клаузи за откупуване</span>
  </div>
</div>

<div class="scores-bar">
  <div class="scores-scroll">
    <div class="score-card">
      <div class="score-league">🏆 Световно първенство по футбол 2026<br> Шестнайсетина финали</div>
      <div class="score-teams">
        <div class="score-row"><span>Южна Африка</span><span class="score-num">0</span></div>
        <div class="score-row"><span>Канада</span><span class="score-num">1</span></div>
      </div>
      <span style="font-size:12px;color:var(--muted);margin-top:6px;">Кр. реж</span>
    </div>
    <div class="score-card">
      <div class="score-league">🏀 НБА - Плейофи - Финал</div>
      <div class="score-teams">
        <div class="score-row"><span>Сан Антонио Спърс </span><span class="score-num">90</span></div>
        <div class="score-row"><span>Ню Йорк Никс</span><span class="score-num">94</span></div>
      </div>
      <span style="font-size:12px;color:var(--muted);margin-top:6px;">Кр. реж</span>
    </div>
    <div class="score-card">
      <div class="score-league">🏎️ Формула 1</div>
      <div class="score-teams">
        <div class="score-row"><span>Ръсел</span><span class="score-num">P1</span></div>
        <div class="score-row"><span>Верстапен</span><span class="score-num">P2</span></div>
      </div>
      <span class="score-live">Квалификация</span>
    </div>
    <div class="score-card">
      <div class="score-league">🏏 Test Series</div>
      <div class="score-teams">
        <div class="score-row"><span>Зимбабве</span><span class="score-num">410</span></div>
        <div class="score-row"><span>Бангладеш</span><span class="score-num">140 & 15/0</span></div>
      </div>
      <div class="score-live">Кр. реж.</div>
    </div>
    <div class="score-card">
      <div class="score-league">🎾 Уимбълдън, 1/64-финали</div>
      <div class="score-teams">
        <div class="score-row"><span>Сун Ву</span><span class="score-num">0-0</span></div>
        <div class="score-row"><span>Ландалусе</span><span class="score-num">1-0</span></div>
      </div>
      <span class="score-live">На живо · Сет 1</span>
    </div>
    <div class="score-card">
      <div class="score-league">🏉 Шампионат на нациите <br>Ръгби</div>
      <div class="score-teams">
        <div class="score-row"><span>Франция</span><span class="score-num"></span></div>
        <div class="score-row"><span>Нова Зеландия</span><span class="score-num"></span></div>
      </div>
      <div class="score-live">8:10 - 4.07</div>
    </div>
  </div>
</div>

<div class="hero">
  <?php if ($hero): ?>
  <a href="article.php?id=<?= $hero['id'] ?>" class="hero-main" style="text-decoration:none;color:inherit;">
    <?php if (!empty($hero['image'])): ?>
      <img src="<?= htmlspecialchars($hero['image']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
    <?php else: ?>
      <div class="hero-placeholder"><?= $hero['emoji'] ?></div>
    <?php endif; ?>
    <div class="hero-overlay">
      <span class="tag">⚡ Най-ново</span>
      <h1 class="hero-title"><?= htmlspecialchars($hero['title']) ?></h1>
      <div class="hero-meta">
        <span>От <?= htmlspecialchars($hero['author']) ?></span>
        <span>·</span>
        <span><?= date('d.m.Y', strtotime($hero['created_at'])) ?></span>
      </div>
    </div>
  </a>
  <?php else: ?>
  <div class="hero-main">
    <div class="hero-placeholder">⚽</div>
    <div class="hero-overlay">
      <span class="tag">⚡ Добре дошли!</span>
      <h1 class="hero-title"><?= t('welcome_title') ?></h1>
      <?php if ($isLoggedIn): ?>
      <div class="hero-meta"><a href="/sportnews/admin/add_article.php" style="color:#fff;">→ Напиши своята първа статия!</a></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="hero-side">
    <?php $side = array_slice($articles, 0, 4); ?>
    <?php if (empty($side)): ?>
      <div class="side-card">
        <div class="side-thumb">📰</div>
        <div class="side-content">
          <div class="side-tag">Да започваме</div>
          <div class="side-title">Добавете статии от Админ панела, за да ги видите тук</div>
          <div class="side-time">Сега</div>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($side as $a): ?>
      <a href="article.php?id=<?= $a['id'] ?>" class="side-card" style="text-decoration:none;color:inherit;">
        <div class="side-thumb">
          <?php if (!empty($a['image'])): ?>
            <img src="<?= htmlspecialchars($a['image']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
          <?php else: ?>
            <?= $a['emoji'] ?>
          <?php endif; ?>
        </div>
        <div class="side-content">
          <div class="side-tag"><?= htmlspecialchars($a['category']) ?></div>
          <div class="side-title"><?= htmlspecialchars($a['title']) ?></div>
          <div class="side-time"><?= date('d.m.Y', strtotime($a['created_at'])) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<section class="section">
  <div class="section-header">
    <div class="section-line"></div>
    <h2>Последни новини</h2>
  </div>
  <div class="card-grid">
    <?php $grid = array_slice($articles, 4); ?>
    <?php if (empty($grid) && empty($side) && !$hero): ?>
      <p style="color:var(--muted);grid-column:1/-1;">Няма статии <?php if ($isLoggedIn): ?><a href="/sportnews/admin/add_article.php" style="color:var(--red);">Добави първата статия</a><?php endif; ?></p>
    <?php else: ?>
      <?php foreach ($grid as $a): ?>
      <a href="article.php?id=<?= $a['id'] ?>" class="news-card" style="text-decoration:none;color:inherit;">
        <div class="card-thumb">
          <?php if (!empty($a['image'])): ?>
            <img src="<?= htmlspecialchars($a['image']) ?>" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <?= $a['emoji'] ?>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="card-tag"><?= htmlspecialchars($a['category']) ?></div>
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
    <?php endif; ?>
  </div>
</section>

<footer>
  <strong>⚡ SportPulse</strong>
  Твоят източник на актуални спортни новини, резултати и анализи.<br><br>
  <a href="about_us.php">За нас</a>
  <a href="privacy_policy.php">Политика за поверителност</a>
  <a href="cookies.php">Бисквитки</a>
  <a href="contact.php">Контакти</a>
  <br><br>
  <span style="font-size:12px;">© <?= date('Y') ?> SportPulse. Всички права запазени.</span>
</footer>

<div id="cookie-banner">
  <span class="cookie-icon">🍪</span>
  <div class="cookie-text">
    <strong>Бисквитки</strong><br>
    Този сайт използва бисквитки.
  </div>
  <div class="cookie-btns">
    <button class="btn-decline" onclick="declineCookies()">Откажи</button>
    <button class="btn-accept" onclick="acceptCookies()">Приеми всички</button>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
function checkCookies() {
    if (localStorage.getItem('sp_cookies')) document.getElementById('cookie-banner').style.display = 'none';
}
function acceptCookies() { localStorage.setItem('sp_cookies','accepted'); hideBanner(); showToast('🍪 Бисквитките са приети!'); }
function declineCookies() { localStorage.setItem('sp_cookies','declined'); hideBanner(); showToast('Бисквитките са отказани.'); }
function hideBanner() {
    const b = document.getElementById('cookie-banner');
    b.style.transform = 'translateY(100%)'; b.style.transition = 'transform 0.4s ease';
    setTimeout(() => b.style.display = 'none', 400);
}

const hamburgerBtn  = document.getElementById('hamburgerBtn');
const pagesDropdown = document.getElementById('pagesDropdown');

if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        pagesDropdown.classList.toggle('open');
        profileMenu && profileMenu.classList.remove('open');
    });
}

const profileAvatar = document.getElementById('profileAvatar');
const profileMenu   = document.getElementById('profileMenu');

if (profileAvatar) {
    profileAvatar.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.classList.toggle('open');
    });
}
document.addEventListener('click', function() {
    profileMenu && profileMenu.classList.remove('open');
});

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(window._tt);
    window._tt = setTimeout(() => t.classList.remove('show'), 3000);
}

window.addEventListener('load', checkCookies);
</script>
</body>
</html>