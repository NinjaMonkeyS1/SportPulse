<?php
session_start();
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("Location: index.php");
    exit;
}

$isLoggedIn = isset($_SESSION['user']);
$is_saved = false; // По подразбиране не е запазена
$date_format_setting = 'DD/MM/YYYY'; // Формат по подразбиране за гости

if ($isLoggedIn) {
    $uname    = $_SESSION['user']['name'];
    $uemail   = $_SESSION['user']['email'];
    $parts    = explode(' ', trim($uname));
    
    // ПОПРАВКА: Използваме mb_substr за кирилица
    $first_letter = mb_substr($parts[0], 0, 1, 'UTF-8');
    $last_letter = count($parts) > 1 ? mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') : mb_substr($parts[0], 1, 1, 'UTF-8');
    $initials = mb_strtoupper($first_letter . $last_letter, 'UTF-8');
    
    $display  = $parts[0] . (count($parts) > 1 ? ' ' . mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8') . '.' : '');

    // Проверка дали ТЕКУЩАТА статия е вече запазена от този потребител
    $check_saved = $pdo->prepare("SELECT id FROM saved_articles WHERE user_email = ? AND article_id = ?");
    $check_saved->execute([$uemail, $article['id']]);
    if ($check_saved->fetch()) {
        $is_saved = true;
    }

    // 1. ВЗИМАНЕ НА НАСТРОЙКАТА ЗА ДАТА НА ПОТРЕБИТЕЛЯ
    $stmt_settings = $pdo->prepare("SELECT dateformat FROM user_settings WHERE user_email = ?");
    $stmt_settings->execute([$uemail]);
    $user_settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);
    if ($user_settings && !empty($user_settings['dateformat'])) {
        $date_format_setting = $user_settings['dateformat'];
    }
}

// 2. ПРЕВРЪЩАНЕ НА СТРИНГ ФОРМАТА В PHP СЪВМЕСТИМ ФОРМАТ
// Превеждаме DD/MM/YYYY, MM/DD/YYYY и YYYY-MM-DD към формати за PHP date()
switch ($date_format_setting) {
    case 'MM/DD/YYYY':
        $php_date_format = 'm/d/Y · H:i';
        break;
    case 'YYYY-MM-DD':
        $php_date_format = 'Y-m-d · H:i';
        break;
    case 'DD/MM/YYYY':
    default:
        $php_date_format = 'd/m/Y · H:i';
        break;
}

// Can the current user delete this article? (must be the author)
$canDelete = $isLoggedIn && isset($uname) && $uname === $article['author'];

// Related articles (same category, excluding current)
$stmt = $pdo->prepare("SELECT * FROM articles WHERE category = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$article['category'], $article['id']]);
$related = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Comments
$stmt = $pdo->prepare("SELECT * FROM comments WHERE article_id = ? ORDER BY created_at DESC");
$stmt->execute([$article['id']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$authorInitials = mb_strtoupper(mb_substr($article['author'], 0, 2, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($article['title']) ?> — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
.article-wrap { max-width: 760px; margin: 0 auto; padding: 32px 24px 60px; }

.back-link {
  display:inline-flex; align-items:center; gap:6px;
  font-size:13px; font-weight:600; color:var(--text-muted);
  text-decoration:none; margin-bottom:24px; transition:color 0.15s;
}
.back-link:hover { color:#e63946; }

.article-tag {
  display:inline-block; background:#e63946; color:#fff;
  font-size:11px; font-weight:700; letter-spacing:1px;
  text-transform:uppercase; padding:5px 14px; border-radius:20px;
  margin-bottom:16px;
}

.article-title {
  font-size:36px; font-weight:900; line-height:1.15;
  letter-spacing:-1px; margin-bottom:18px; color:var(--text-main);
}

.article-meta {
  display:flex; align-items:center; gap:14px;
  padding-bottom:24px; margin-bottom:28px;
  border-bottom:1px solid var(--border-c);
}
.meta-author { display:flex; align-items:center; gap:10px; }
.meta-avatar {
  width:38px; height:38px; border-radius:50%;
  background:#e63946; color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:13px; font-weight:800;
}
.meta-name { font-size:14px; font-weight:700; color:var(--text-main); }
.meta-date { font-size:12px; color:var(--text-faint); }
.meta-divider { width:1px; height:30px; background:var(--border-c); }
.meta-stat { font-size:13px; color:var(--text-muted); }

.article-hero-img {
  width:100%; aspect-ratio:16/9; border-radius:16px;
  object-fit:cover; margin-bottom:28px; display:block;
}
.article-hero-placeholder {
  width:100%; aspect-ratio:16/9; border-radius:16px;
  background:linear-gradient(135deg,#1a1d27 0%,#2d1a20 50%,#1a2020 100%);
  display:flex; align-items:center; justify-content:center;
  font-size:90px; margin-bottom:28px;
}

.article-excerpt {
  font-size:18px; font-weight:500; color:var(--text-main);
  line-height:1.6; margin-bottom:28px; padding-left:18px;
  border-left:3px solid #e63946;
}

.article-body {
  font-size:17px; line-height:1.85; color:var(--text-main);
}
.article-body h2 { font-size:26px; font-weight:800; margin:32px 0 12px; color:var(--text-main); }
.article-body h3 { font-size:21px; font-weight:700; margin:24px 0 10px; color:var(--text-main); }
.article-body p { margin-bottom:18px; }
.article-body strong { font-weight:700; }
.article-body em { font-style:italic; }
.article-body ul, .article-body ol { padding-left:26px; margin-bottom:18px; }
.article-body li { margin-bottom:8px; }
.article-body blockquote {
  border-left:4px solid #e63946; padding:16px 24px;
  background:rgba(230,57,70,0.1); border-radius:0 12px 12px 0;
  margin:24px 0; font-style:italic; font-size:18px; color:var(--text-main);
}
.article-body a { color:#e63946; text-decoration:underline; }
.article-body hr { border:none; border-top:2px solid var(--border-c); margin:28px 0; }

/* SHARE / ACTIONS */
.article-actions {
  display:flex; align-items:center; justify-content:space-between;
  padding:24px 0; margin:32px 0; border-top:1px solid var(--border-c); border-bottom:1px solid var(--border-c);
}
.action-btns { display:flex; gap:10px; }
.action-btn {
  display:flex; align-items:center; gap:7px;
  padding:9px 16px; border-radius:10px;
  border:1.5px solid var(--border-c); background:var(--bg-card); color:var(--text-main);
  font-family:'Inter',sans-serif; font-size:13px; font-weight:600;
  cursor:pointer; transition:all 0.2s;
}
.action-btn:hover { border-color:#e63946; color:#e63946; background:rgba(230,57,70,0.1); }
.action-btn.active { background:#e63946; border-color:#e63946; color:#fff; }

/* AUTHOR CARD */
.author-card {
  display:flex; gap:16px; align-items:center;
  background:var(--bg-card2); border:1px solid var(--border-c); border-radius:16px;
  padding:20px; margin-bottom:36px;
}
.author-avatar-lg {
  width:56px; height:56px; border-radius:50%;
  background:linear-gradient(135deg,#e63946,#ff6b6b); color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:20px; font-weight:800; flex-shrink:0;
}
.author-info h4 { font-size:15px; font-weight:700; margin-bottom:2px; }
.author-info p { font-size:13px; color:var(--text-muted); }

/* RELATED */
.related-section { margin-top:48px; }
.related-title { font-size:20px; font-weight:800; margin-bottom:20px; letter-spacing:-0.3px; }
.related-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.related-card {
  background:var(--bg-card); border:1.5px solid var(--border-c); border-radius:14px;
  overflow:hidden; text-decoration:none; color:inherit;
  transition:all 0.25s; display:block;
}
.related-card:hover { transform:translateY(-4px); border-color:#e63946; box-shadow:0 12px 30px rgba(0,0,0,0.08); }
.related-thumb {
  height:110px; background:var(--bg-card2);
  display:flex; align-items:center; justify-content:center;
  font-size:36px; overflow:hidden;
}
.related-thumb img { width:100%; height:100%; object-fit:cover; }
.related-body { padding:14px; }
.related-cat { font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#e63946; margin-bottom:6px; }
.related-card-title { font-size:14px; font-weight:700; line-height:1.3; color:var(--text-main); }

/* COMMENTS */
.comments-section { margin-top:48px; }
.comments-title { font-size:20px; font-weight:800; margin-bottom:20px; letter-spacing:-0.3px; display:flex; align-items:center; gap:8px; }
.comments-count { background:rgba(230,57,70,0.1); color:#e63946; font-size:13px; font-weight:700; padding:2px 10px; border-radius:20px; }

.comment-form {
  background:var(--bg-card2); border:1px solid var(--border-c); border-radius:14px;
  padding:18px; margin-bottom:24px; display:flex; gap:12px;
}
.comment-form-avatar {
  width:38px; height:38px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg,#e63946,#ff6b6b); color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:13px; font-weight:800;
}
.comment-form-body { flex:1; }
.comment-form textarea {
  width:100%; border:1.5px solid var(--border-c); border-radius:10px;
  padding:12px 14px; font-family:'Inter',sans-serif; font-size:14px;
  outline:none; resize:vertical; min-height:70px; transition:border-color 0.2s;
  background:var(--bg-card);
}
.comment-form textarea:focus { border-color:#e63946; }
.comment-form-actions { display:flex; justify-content:flex-end; margin-top:10px; }
.btn-comment-submit {
  background:#e63946; color:#fff; border:none;
  padding:9px 22px; border-radius:8px;
  font-family:'Inter',sans-serif; font-weight:700; font-size:13px;
  cursor:pointer; transition:all 0.2s;
}
.btn-comment-submit:hover { background:#c1121f; }

.comment-login-prompt {
  background:var(--bg-card2); border:1px solid var(--border-c); border-radius:14px;
  padding:20px; text-align:center; margin-bottom:24px;
}
.comment-login-prompt p { font-size:14px; color:var(--text-muted); margin-bottom:12px; }
.comment-login-prompt a {
  display:inline-block; background:#e63946; color:#fff;
  padding:9px 22px; border-radius:8px; text-decoration:none;
  font-weight:700; font-size:13px; transition:all 0.2s;
}
.comment-login-prompt a:hover { background:#c1121f; }

.comment-item { display:flex; gap:12px; padding:18px 0; border-bottom:1px solid #f3f4f6; }
.comment-item:last-child { border-bottom:none; }
.comment-avatar {
  width:38px; height:38px; border-radius:50%; flex-shrink:0;
  background:#374151; color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:13px; font-weight:800;
}
.comment-body { flex:1; }
.comment-head { display:flex; align-items:center; gap:10px; margin-bottom:5px; }
.comment-name { font-size:14px; font-weight:700; color:var(--text-main); }
.comment-time { font-size:12px; color:var(--text-faint); }
.comment-text { font-size:14px; color:var(--text-main); line-height:1.6; }
.comment-delete {
  font-size:12px; color:#e63946; text-decoration:none;
  font-weight:600; margin-top:6px; display:inline-block;
}
.comment-delete:hover { text-decoration:underline; }

.no-comments { text-align:center; padding:32px; color:var(--text-faint); font-size:14px; }

@media (max-width: 700px) {
  .article-title { font-size:26px; }
  .related-grid { grid-template-columns:1fr; }
  .article-actions { flex-direction:column; gap:14px; align-items:flex-start; }
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
      <?php if (isset($_SESSION['user'])): ?>
        <div class="profile-avatar" id="profileAvatar">
          <div class="avatar-circle"><?= $initials ?? '??' ?></div>
          <span class="avatar-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Потребител') ?></span>
          <div class="profile-menu" id="profileMenu">
            <ul>
              <li><a href="profile.php">👤 Моят профил</a></li>
              <li><a href="saved.php">⭐ Запазени статии</a></li>
              <li><a href="settings.php">⚙️ Настройки</a></li>
              <li><a href="admin/add_article.php">📝 Писане на статия</a></li>
              <li class="danger"><a href="logout.php">🚪 Изход</a></li>
            </ul>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php" class="btn-login">Вход</a>
        <a href="register.php" class="btn-login btn-outline">Регистриране</a>
      <?php endif; ?>
    </div>
  </div> 
</nav>
<div class="article-wrap">

  <a href="index.php" class="back-link">Обратно</a>

  <span class="article-tag"><?= htmlspecialchars($article['category']) ?></span>

  <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>

  <div class="article-meta">
    <div class="meta-author">
      <div class="meta-avatar"><?= $authorInitials ?></div>
      <div>
        <div class="meta-name"><?= htmlspecialchars($article['author']) ?></div>
        <div class="meta-date"><?= date($php_date_format, strtotime($article['created_at'])) ?></div>
      </div>
    </div>
    <div class="meta-divider"></div>
    <div class="meta-stat">📖 <?= max(1, ceil(str_word_count(strip_tags($article['content'])) / 200)) ?> мин четене</div>
  </div>

  <?php if (!empty($article['image'])): ?>
    <img src="<?= htmlspecialchars($article['image']) ?>" class="article-hero-img" alt="<?= htmlspecialchars($article['title']) ?>">
  <?php else: ?>
    <div class="article-hero-placeholder"><?= $article['emoji'] ?></div>
  <?php endif; ?>

  <div class="article-excerpt"><?= htmlspecialchars($article['excerpt']) ?></div>

  <div class="article-body"><?= $article['content'] ?></div>

  <div class="article-actions">
    <div class="action-btns">
      <button class="action-btn <?= $is_saved ? 'active' : '' ?>" id="saveBtn" data-id="<?= $article['id'] ?>" onclick="toggleSave()">
         <?= $is_saved ? '⭐ Запазена' : '⭐ Запази' ?>
      </button>
      <button class="action-btn" onclick="shareArticle()">🔗 Сподели</button>
    </div>
    
    <div class="action-btns">
      <?php if ($canDelete): ?>
        <a href="/sportnews/admin/delete_article.php?id=<?= $article['id'] ?>&redirect=home"
           class="action-btn"
           style="border-color:#fca5a5;color:#e63946;"
           onclick="return confirm('Изтриване на статията? Действието е необратимо.')">🗑️ Изтрий</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="author-card">
    <div class="author-avatar-lg"><?= $authorInitials ?></div>
    <div class="author-info">
      <h4><?= htmlspecialchars($article['author']) ?></h4>
      <p>Автор в SportPulse · <?= htmlspecialchars($article['category']) ?> Кореспондент</p>
    </div>
  </div>

  <?php if (!empty($related)): ?>
  <div class="related-section">
    <div class="related-title">Още от <?= htmlspecialchars($article['category']) ?></div>
    <div class="related-grid">
      <?php foreach ($related as $r): ?>
      <a href="article.php?id=<?= $r['id'] ?>" class="related-card">
        <div class="related-thumb">
          <?php if (!empty($r['image'])): ?>
            <img src="<?= htmlspecialchars($r['image']) ?>" alt="">
          <?php else: ?>
            <?= $r['emoji'] ?>
          <?php endif; ?>
        </div>
        <div class="related-body">
          <div class="related-cat"><?= htmlspecialchars($r['category']) ?></div>
          <div class="related-card-title"><?= htmlspecialchars($r['title']) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="comments-section" id="comments">
    <div class="comments-title">
      💬 Коментари
      <span class="comments-count"><?= count($comments) ?></span>
    </div>

    <?php if ($isLoggedIn): ?>
      <form method="POST" action="add_comment.php" class="comment-form">
        <div class="comment-form-avatar"><?= $initials ?></div>
        <div class="comment-form-body">
          <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
          <textarea name="comment" placeholder="Сподели мнението си за статията..." required></textarea>
          <div class="comment-form-actions">
            <button type="submit" class="btn-comment-submit">Напиши коментар</button>
          </div>
        </div>
      </form>
    <?php else: ?>
      <div class="comment-login-prompt">
        <p>Влез за да коментираш</p>
        <a href="login.php">Влез</a>
      </div>
    <?php endif; ?>

    <?php if (empty($comments)): ?>
      <div class="no-comments">Няма коментари</div>
    <?php else: ?>
      <?php foreach ($comments as $c): ?>
        <?php
          $cParts = explode(' ', trim($c['user_name']));
          $cFirst = mb_substr($cParts[0], 0, 1, 'UTF-8');
          $cLast = count($cParts) > 1 ? mb_substr($cParts[count($cParts)-1], 0, 1, 'UTF-8') : mb_substr($cParts[0], 1, 1, 'UTF-8');
          $cInitials = mb_strtoupper($cFirst . $cLast, 'UTF-8');
        ?>
        <div class="comment-item">
          <div class="comment-avatar"><?= $cInitials ?></div>
          <div class="comment-body">
            <div class="comment-head">
              <span class="comment-name"><?= htmlspecialchars($c['user_name']) ?></span>
              <span class="comment-time"><?= date($php_date_format, strtotime($c['created_at'])) ?></span>
            </div>
            <div class="comment-text"><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
            <?php if ($isLoggedIn && $c['user_email'] === $uemail): ?>
              <a href="delete_comment.php?id=<?= $c['id'] ?>&article_id=<?= $article['id'] ?>"
                 class="comment-delete"
                 onclick="return confirm('Искаш ли да изтриеш коментара?')">Изтрий</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<footer>
  <strong>⚡ SportPulse</strong>
  Вашият източник за спортни новини, резултати и анализи на живо.<br><br>
  <a href="index.php">Начало</a>
  <a href="#">За нас </a>
  <a href="privacy_policy.php">Политика за поверителност</a>
  <a href="#">Контакт</a>
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

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(window._tt);
    window._tt = setTimeout(() => t.classList.remove('show'), 3000);
}

function toggleSave() {
    const btn = document.getElementById('saveBtn');
    const articleId = btn.getAttribute('data-id');

    if (!<?= json_encode($isLoggedIn) ?>) {
        showToast('🔒 Моля, влезте в профила си, за да запазвате статии.');
        setTimeout(() => window.location.href = 'login.php', 1500);
        return;
    }

    const formData = new FormData();
    formData.append('article_id', articleId);

    fetch('toggle_save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.status === 'saved') {
                btn.classList.add('active');
                btn.innerHTML = '⭐ Запазена';
                showToast('⭐ Статията е запазена!');
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '⭐ Запази';
                showToast('Премахнато от запазените.');
            }
        } else {
            showToast('Възникна грешка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Грешка при комуникация със сървъра.');
    });
}

function shareArticle() {
    if (navigator.share) {
        navigator.share({ title: document.title, url: window.location.href });
    } else {
        navigator.clipboard.writeText(window.location.href);
        showToast('🔗 Линкът е копиран!');
    }
}
</script>

<div id="progressBar" style="position:fixed; top:0; left:0; height:3px; background:#e63946; width:0%; z-index:9999;"></div>

<script>
window.onscroll = function() {
    let winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    let height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    let scrolled = (winScroll / height) * 100;
    document.getElementById("progressBar").style.width = scrolled + "%";
};
</script>
</body>
</html>