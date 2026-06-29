<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$uname   = $_SESSION['user']['name'];
$uemail  = $_SESSION['user']['email'];
$parts   = explode(' ', trim($uname));
$initials = strtoupper(substr($parts[0],0,1) . (count($parts)>1 ? substr($parts[count($parts)-1],0,1) : substr($parts[0],1,1)));

$notifications = [
  ['icon'=>'🎉','title'=>'Welcome to SportPulse!','desc'=>'Thanks for joining. Start reading the latest sports news now.','time'=>'Just now','type'=>'info','unread'=>true],
  ['icon'=>'⚽','title'=>'Champions League Final Tonight','desc'=>'Real Madrid vs Bayern Munich kicks off at 20:00 CET. Don\'t miss it!','time'=>'2 hours ago','type'=>'sport','unread'=>true],
  ['icon'=>'🏀','title'=>'NBA Game 7 Alert','desc'=>'Celtics vs Heat — the decisive match is live right now!','time'=>'3 hours ago','type'=>'live','unread'=>true],
  ['icon'=>'🏎️','title'=>'F1 Qualifying Results','desc'=>'Verstappen takes pole at Monaco GP. Race is on Sunday at 15:00.','time'=>'5 hours ago','type'=>'sport','unread'=>false],
  ['icon'=>'🎾','title'=>'Roland Garros Semi-Final','desc'=>'Alcaraz faces Sinner in what promises to be a classic match.','time'=>'Yesterday','type'=>'sport','unread'=>false],
  ['icon'=>'⭐','title'=>'New article in Football','desc'=>'Manchester United Eye Summer Swoop For Brazilian Sensation.','time'=>'2 days ago','type'=>'news','unread'=>false],
  ['icon'=>'🔔','title'=>'Weekly Digest Ready','desc'=>'Your personalised sports summary for this week is ready to read.','time'=>'3 days ago','type'=>'digest','unread'=>false],
];

$unreadCount = count(array_filter($notifications, fn($n) => $n['unread']));

$typeColors = [
  'info'   => ['bg'=>'#eff6ff','color'=>'#3b82f6','border'=>'#bfdbfe'],
  'sport'  => ['bg'=>'#fff0f1','color'=>'#e63946','border'=>'#fca5a5'],
  'live'   => ['bg'=>'#fff7ed','color'=>'#f97316','border'=>'#fdba74'],
  'news'   => ['bg'=>'#f0fdf4','color'=>'#16a34a','border'=>'#86efac'],
  'digest' => ['bg'=>'#faf5ff','color'=>'#9333ea','border'=>'#d8b4fe'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications — SportPulse</title>
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

.page { max-width:800px; margin:0 auto; padding:32px 24px; }

/* HEADER */
.page-header {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:24px; animation: fadeUp 0.3s ease;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);} }

.page-title { font-size:26px; font-weight:900; letter-spacing:-0.5px; display:flex; align-items:center; gap:10px; }
.unread-badge {
  background:#e63946; color:#fff;
  font-size:12px; font-weight:700;
  padding:3px 10px; border-radius:20px;
}

.header-actions { display:flex; gap:8px; }
.btn-ghost {
  background:#fff; border:1.5px solid #e5e7eb; color:#6b7280;
  padding:8px 16px; border-radius:8px; font-family:'Inter',sans-serif;
  font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s;
}
.btn-ghost:hover { border-color:#e63946; color:#e63946; background:#fff0f1; }

/* FILTER TABS */
.filter-tabs {
  display:flex; gap:6px; margin-bottom:20px;
  animation: fadeUp 0.3s 0.05s ease both;
}
.filter-tab {
  padding:7px 16px; border-radius:20px; font-size:13px; font-weight:600;
  background:#fff; border:1.5px solid #e5e7eb; color:#6b7280;
  cursor:pointer; transition:all 0.15s;
}
.filter-tab.active, .filter-tab:hover { background:#e63946; border-color:#e63946; color:#fff; }

/* NOTIFICATIONS */
.notif-list { display:flex; flex-direction:column; gap:12px; }

.notif-card {
  background:#fff; border:1.5px solid #e5e7eb; border-radius:14px;
  padding:18px 20px; display:flex; gap:16px; align-items:flex-start;
  transition:all 0.2s; cursor:pointer;
  animation: fadeUp 0.3s ease both;
}
.notif-card:nth-child(1){animation-delay:0.05s;}
.notif-card:nth-child(2){animation-delay:0.1s;}
.notif-card:nth-child(3){animation-delay:0.15s;}
.notif-card:nth-child(4){animation-delay:0.2s;}
.notif-card:nth-child(5){animation-delay:0.25s;}

.notif-card:hover { border-color:#e63946; box-shadow:0 4px 16px rgba(230,57,70,0.08); transform:translateY(-1px); }
.notif-card.unread { border-left:3.5px solid #e63946; }

.notif-icon-wrap {
  width:44px; height:44px; border-radius:12px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:20px;
}

.notif-content { flex:1; }
.notif-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:5px; }
.notif-title { font-size:15px; font-weight:700; }
.notif-time { font-size:11px; color:#9ca3af; white-space:nowrap; margin-top:2px; }
.notif-desc { font-size:13px; color:#6b7280; line-height:1.55; }
.notif-actions { display:flex; gap:8px; margin-top:10px; }
.notif-btn {
  padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600;
  border:1.5px solid #e5e7eb; background:#f8f9fc; color:#374151;
  cursor:pointer; font-family:'Inter',sans-serif; transition:all 0.15s;
}
.notif-btn:hover { border-color:#e63946; color:#e63946; }
.notif-btn.primary { background:#e63946; color:#fff; border-color:#e63946; }
.notif-btn.primary:hover { background:#c1121f; }
.unread-dot { width:9px; height:9px; background:#e63946; border-radius:50%; flex-shrink:0; margin-top:6px; }

/* EMPTY STATE */
.empty {
  text-align:center; padding:60px 24px;
  background:#fff; border:1px solid #e5e7eb; border-radius:16px;
}
.empty .icon { font-size:48px; margin-bottom:14px; }
.empty h3 { font-size:18px; font-weight:700; margin-bottom:6px; }
.empty p { font-size:14px; color:#6b7280; }
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo"><div class="logo-dot"></div>Sport<span>Pulse</span></a>
    <div class="nav-right">
      <a href="index.php" class="nav-link">← Home</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="settings.php" class="nav-link">Settings</a>
      <a href="logout.php" class="nav-link danger">Sign Out</a>
      <div class="nav-user">
        <div class="nav-avatar"><?= $initials ?></div>
        <?= htmlspecialchars($parts[0]) ?>
      </div>
    </div>
  </div>
</nav>

<div class="page">

  <div class="page-header">
    <div class="page-title">
      🔔 Notifications
      <?php if ($unreadCount > 0): ?>
        <span class="unread-badge"><?= $unreadCount ?> new</span>
      <?php endif; ?>
    </div>
    <div class="header-actions">
      <button class="btn-ghost" onclick="markAllRead()">✓ Mark all read</button>
      <button class="btn-ghost" onclick="clearAll()">🗑 Clear all</button>
    </div>
  </div>

  <div class="filter-tabs">
    <div class="filter-tab active" onclick="filterNotifs('all',this)">All</div>
    <div class="filter-tab" onclick="filterNotifs('unread',this)">Unread</div>
    <div class="filter-tab" onclick="filterNotifs('sport',this)">Sport</div>
    <div class="filter-tab" onclick="filterNotifs('news',this)">News</div>
  </div>

  <div class="notif-list" id="notifList">
    <?php foreach ($notifications as $i => $n):
      $c = $typeColors[$n['type']];
    ?>
    <div class="notif-card <?= $n['unread'] ? 'unread' : '' ?>" data-type="<?= $n['type'] ?>" data-unread="<?= $n['unread'] ? '1' : '0' ?>" id="notif-<?= $i ?>">
      <div class="notif-icon-wrap" style="background:<?= $c['bg'] ?>;border:1px solid <?= $c['border'] ?>;">
        <?= $n['icon'] ?>
      </div>
      <div class="notif-content">
        <div class="notif-header">
          <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
          <div class="notif-time"><?= $n['time'] ?></div>
        </div>
        <div class="notif-desc"><?= htmlspecialchars($n['desc']) ?></div>
        <div class="notif-actions">
          <button class="notif-btn primary">View</button>
          <button class="notif-btn" onclick="dismissNotif(<?= $i ?>)">Dismiss</button>
        </div>
      </div>
      <?php if ($n['unread']): ?>
      <div class="unread-dot" id="dot-<?= $i ?>"></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
function markAllRead() {
    document.querySelectorAll('.notif-card').forEach(c => c.classList.remove('unread'));
    document.querySelectorAll('.unread-dot').forEach(d => d.remove());
    document.querySelector('.unread-badge') && (document.querySelector('.unread-badge').style.display='none');
}

function clearAll() {
    if (confirm('Clear all notifications?')) {
        const list = document.getElementById('notifList');
        list.innerHTML = '<div class="empty"><div class="icon">📭</div><h3>All clear!</h3><p>No notifications right now.</p></div>';
    }
}

function dismissNotif(id) {
    const el = document.getElementById('notif-'+id);
    el.style.opacity = '0';
    el.style.transform = 'translateX(20px)';
    el.style.transition = 'all 0.3s';
    setTimeout(() => el.remove(), 300);
}

function filterNotifs(type, btn) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.notif-card').forEach(c => {
        if (type === 'all') { c.style.display = ''; }
        else if (type === 'unread') { c.style.display = c.dataset.unread === '1' ? '' : 'none'; }
        else { c.style.display = c.dataset.type === type ? '' : 'none'; }
    });
}
</script>
</body>
</html>
