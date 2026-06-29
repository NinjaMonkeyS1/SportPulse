<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$uname   = $_SESSION['user']['name'];
$uemail  = $_SESSION['user']['email'];
$parts   = explode(' ', trim($uname));
$initials = strtoupper(substr($parts[0],0,1) . (count($parts)>1 ? substr($parts[count($parts)-1],0,1) : substr($parts[0],1,1)));

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: profile.php"); exit; }

// Load article — only allow editing your own
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND author = ?");
$stmt->execute([$id, $uname]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("Location: profile.php");
    exit;
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $title    = trim($_POST['title']);
    $excerpt  = trim($_POST['excerpt']);
    $content  = trim($_POST['content']);
    $category = trim($_POST['category']);
    $league   = trim($_POST['league'] ?? '');
    $emoji    = trim($_POST['emoji']);
    $image    = $article['image']; // keep existing image by default

    // Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','webp','gif'];
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $filename = uniqid('img_') . '.' . $ext;
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                // Delete old image
                if (!empty($article['image']) && file_exists($article['image'])) {
                    unlink($article['image']);
                }
                $image = 'uploads/' . $filename;
            }
        } else {
            $error = "Invalid image. Use JPG, PNG or WEBP under 5MB.";
        }
    }

    // Remove image if requested
    if (isset($_POST['remove_image']) && !empty($article['image'])) {
        if (file_exists($article['image'])) unlink($article['image']);
        $image = null;
    }

    if (!$error && $title && $excerpt && $content && $category && $emoji) {
        $stmt = $pdo->prepare("UPDATE articles SET title=?, excerpt=?, content=?, category=?, league=?, emoji=?, image=? WHERE id=? AND author=?");
        $stmt->execute([$title, $excerpt, $content, $category, $league, $emoji, $image, $id, $uname]);
        $article = array_merge($article, [
            'title'=>$title, 'excerpt'=>$excerpt, 'content'=>$content,
            'category'=>$category, 'league'=>$league, 'emoji'=>$emoji, 'image'=>$image
        ]);
        $success = "Article updated successfully!";
    } elseif (!$error) {
        $error = "Please fill in all fields.";
    }
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Article — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
.edit-layout { max-width:1300px; margin:0 auto; padding:28px 24px; display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start; }
@keyframes fadeUp { from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);} }

.editor-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.04); animation:fadeUp 0.3s ease; }
.editor-topbar { padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; gap:12px; }
.editor-topbar h2 { font-size:16px; font-weight:700; }
.edit-badge { background:#fff0f1; color:#e63946; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; }

.title-input { width:100%; border:none; outline:none; font-family:'Inter',sans-serif; font-size:26px; font-weight:800; color:#0f1117; padding:24px 24px 16px; letter-spacing:-0.5px; background:transparent; border-bottom:1px solid #f3f4f6; }
.title-input::placeholder { color:#d1d5db; font-weight:400; }
.excerpt-input { width:100%; border:none; outline:none; font-family:'Inter',sans-serif; font-size:15px; color:#374151; padding:14px 24px; background:transparent; border-bottom:1px solid #f3f4f6; resize:none; line-height:1.6; min-height:80px; }
.excerpt-input::placeholder { color:#d1d5db; }

.toolbar { display:flex; align-items:center; gap:2px; padding:10px 14px; border-bottom:1px solid #f3f4f6; background:#fafafa; flex-wrap:wrap; }
.toolbar-btn { width:34px; height:34px; border-radius:7px; border:none; background:transparent; cursor:pointer; font-size:13px; display:flex; align-items:center; justify-content:center; color:#374151; transition:all 0.15s; font-family:'Inter',sans-serif; font-weight:700; }
.toolbar-btn:hover { background:#f0f2f7; color:#e63946; }
.toolbar-sep { width:1px; height:22px; background:#e5e7eb; margin:0 4px; flex-shrink:0; }

.content-editor { min-height:380px; padding:20px 24px; outline:none; font-size:15px; line-height:1.8; color:#374151; font-family:'Inter',sans-serif; }
.content-editor h2 { font-size:22px; font-weight:800; margin:18px 0 8px; color:#0f1117; }
.content-editor h3 { font-size:18px; font-weight:700; margin:14px 0 6px; color:#0f1117; }
.content-editor p { margin-bottom:12px; }
.content-editor blockquote { border-left:4px solid #e63946; padding:14px 20px; background:#fff0f1; border-radius:0 10px 10px 0; margin:16px 0; font-style:italic; }
.content-editor ul, .content-editor ol { padding-left:24px; margin-bottom:12px; }
.content-editor li { margin-bottom:5px; }

.editor-footer { padding:10px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:space-between; background:#fafafa; }
.editor-footer span { font-size:12px; color:#9ca3af; }

/* SIDEBAR */
.sidebar { display:flex; flex-direction:column; gap:16px; animation:fadeUp 0.3s 0.05s ease both; }
.sidebar-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.04); }
.sidebar-head { padding:13px 18px; border-bottom:1px solid #f3f4f6; font-size:13px; font-weight:700; color:#374151; background:#fafafa; }

/* PUBLISH */
.pub-box { padding:18px; }
.pub-info { display:flex; flex-direction:column; gap:10px; margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid #f3f4f6; }
.pub-row { display:flex; justify-content:space-between; font-size:13px; }
.pub-label { color:#6b7280; }
.pub-val { font-weight:600; }

.btn-update { width:100%; background:#e63946; color:#fff; border:none; padding:13px; border-radius:10px; font-family:'Inter',sans-serif; font-weight:700; font-size:14px; cursor:pointer; transition:all 0.2s; margin-bottom:8px; }
.btn-update:hover { background:#c1121f; transform:translateY(-1px); box-shadow:0 6px 16px rgba(230,57,70,0.25); }
.btn-secondary { width:100%; background:#f8f9fc; color:#374151; border:1.5px solid #e5e7eb; padding:10px; border-radius:10px; font-family:'Inter',sans-serif; font-weight:600; font-size:13px; cursor:pointer; transition:all 0.2s; text-decoration:none; display:block; text-align:center; margin-top:6px; }
.btn-secondary:hover { border-color:#e63946; color:#e63946; }
.btn-danger-sm { width:100%; background:#fff0f1; color:#e63946; border:1.5px solid #fca5a5; padding:10px; border-radius:10px; font-family:'Inter',sans-serif; font-weight:600; font-size:13px; cursor:pointer; transition:all 0.2s; margin-top:6px; }
.btn-danger-sm:hover { background:#e63946; color:#fff; }

/* IMAGE */
.upload-wrap { padding:16px 18px; }
.current-img { width:100%; height:140px; object-fit:cover; border-radius:10px; margin-bottom:10px; }
.upload-area { border:2px dashed #e5e7eb; border-radius:12px; padding:24px 16px; text-align:center; cursor:pointer; transition:all 0.2s; background:#fafafa; }
.upload-area:hover { border-color:#e63946; background:#fff0f1; }
.upload-icon { font-size:28px; margin-bottom:6px; }
.upload-text { font-size:13px; font-weight:600; color:#374151; margin-bottom:2px; }
.upload-sub { font-size:11px; color:#9ca3af; }

/* CAT + EMOJI */
.cat-grid { padding:14px 18px; display:flex; flex-wrap:wrap; gap:8px; }
.cat-chip { padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; border:1.5px solid #e5e7eb; background:#fff; color:#374151; cursor:pointer; transition:all 0.15s; user-select:none; }
.cat-chip:hover { border-color:#e63946; color:#e63946; }
.cat-chip.selected { background:#e63946; border-color:#e63946; color:#fff; }
.emoji-grid { padding:14px 18px; display:flex; flex-wrap:wrap; gap:8px; }
.emoji-opt { width:38px; height:38px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; font-size:19px; cursor:pointer; transition:all 0.15s; display:flex; align-items:center; justify-content:center; user-select:none; }
.emoji-opt:hover, .emoji-opt.selected { border-color:#e63946; background:#fff0f1; transform:scale(1.1); }
.emoji-custom-wrap { padding:0 18px 14px; }
.emoji-custom-wrap label { display:block; font-size:11px; color:#9ca3af; margin-bottom:5px; font-weight:600; letter-spacing:0.5px; text-transform:uppercase; }
.emoji-custom-wrap input { width:100%; border:1.5px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:18px; outline:none; font-family:'Inter',sans-serif; transition:border-color 0.2s; }
.emoji-custom-wrap input:focus { border-color:#e63946; }
.league-wrap { padding:14px 18px; }
.league-wrap label { display:block; font-size:11px; color:#9ca3af; margin-bottom:6px; font-weight:600; letter-spacing:0.5px; text-transform:uppercase; }
.league-wrap select { width:100%; background:#f8f9fc; border:1.5px solid #e5e7eb; border-radius:10px; padding:10px 14px; font-family:'Inter',sans-serif; font-size:14px; outline:none; transition:border-color 0.2s; }
.league-wrap select:focus { border-color:#e63946; }

.alert { padding:14px 18px; border-radius:10px; font-size:13px; font-weight:600; margin-bottom:16px; display:flex; align-items:center; gap:10px; }
.alert-success { background:#f0fdf4; border:1.5px solid #86efac; color:#16a34a; }
.alert-error   { background:#fff0f1; border:1.5px solid #fca5a5; color:#e63946; }

#categoryInput, #emojiInput, #contentInput { display:none; }

@media (max-width:960px) { .edit-layout { grid-template-columns:1fr; } }
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo"><div class="logo-dot"></div>Sport<span>Pulse</span></a>
    <div class="nav-right">
      <a href="profile.php" class="btn-login" style="background:#f8f9fc;color:#374151;border:1.5px solid #e5e7eb;">← Back to Profile</a>
      <a href="article.php?id=<?= $article['id'] ?>" class="btn-login" style="background:#f8f9fc;color:#374151;border:1.5px solid #e5e7eb;">View Article</a>
      <div class="nav-user" style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;">
        <div class="nav-avatar" style="width:32px;height:32px;border-radius:50%;background:#e63946;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;"><?= $initials ?></div>
        <?= htmlspecialchars($parts[0]) ?>
      </div>
    </div>
  </div>
</nav>

<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="update" value="1">
  <input type="hidden" name="category" id="categoryInput" value="<?= htmlspecialchars($article['category']) ?>">
  <input type="hidden" name="emoji"    id="emojiInput"    value="<?= htmlspecialchars($article['emoji']) ?>">
  <input type="hidden" name="content"  id="contentInput">

  <div class="edit-layout">

    <!-- EDITOR -->
    <div>
      <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="alert alert-error">⚠️ <?= $error ?></div><?php endif; ?>

      <div class="editor-card">
        <div class="editor-topbar">
          <h2>✏️ Редактиране на статията</h2>
          <span class="edit-badge">Редактиране #<?= $article['id'] ?></span>
        </div>

        <input type="text" name="title" class="title-input" value="<?= htmlspecialchars($article['title']) ?>" placeholder="Article headline…" required>
        <textarea name="excerpt" class="excerpt-input" placeholder="Short summary…" required><?= htmlspecialchars($article['excerpt']) ?></textarea>

        <div class="toolbar">
          <div style="display:flex;gap:2px;">
            <button type="button" class="toolbar-btn" onclick="fmt('bold')" title="Bold"><b>B</b></button>
            <button type="button" class="toolbar-btn" onclick="fmt('italic')" title="Italic"><i>I</i></button>
            <button type="button" class="toolbar-btn" onclick="fmt('underline')" title="Underline"><u>U</u></button>
            <button type="button" class="toolbar-btn" onclick="fmt('strikeThrough')" title="Strike"><s>S</s></button>
          </div>
          <div class="toolbar-sep"></div>
          <div style="display:flex;gap:2px;">
            <button type="button" class="toolbar-btn" onclick="fmtBlock('h2')" title="H2" style="font-size:11px;">H2</button>
            <button type="button" class="toolbar-btn" onclick="fmtBlock('h3')" title="H3" style="font-size:11px;">H3</button>
            <button type="button" class="toolbar-btn" onclick="fmtBlock('p')" title="Paragraph">¶</button>
          </div>
          <div class="toolbar-sep"></div>
          <div style="display:flex;gap:2px;">
            <button type="button" class="toolbar-btn" onclick="fmt('insertUnorderedList')" title="Bullets">• ≡</button>
            <button type="button" class="toolbar-btn" onclick="fmt('insertOrderedList')" title="Numbered" style="font-size:11px;">1.</button>
            <button type="button" class="toolbar-btn" onclick="fmtBlock('blockquote')" title="Quote">❝</button>
            <button type="button" class="toolbar-btn" onclick="fmt('insertHorizontalRule')" title="Divider">—</button>
            <button type="button" class="toolbar-btn" onclick="insertLink()" title="Link">🔗</button>
          </div>
          <div class="toolbar-sep"></div>
          <div style="display:flex;gap:2px;">
            <button type="button" class="toolbar-btn" onclick="fmt('undo')">↩</button>
            <button type="button" class="toolbar-btn" onclick="fmt('redo')">↪</button>
          </div>
        </div>

        <div class="content-editor" id="editor" contenteditable="true"></div>

        <div class="editor-footer">
          <span id="wordCount">0 words</span>
          <span id="charCount">0 characters</span>
        </div>
      </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar">

      <!-- SAVE / ACTIONS -->
      <div class="sidebar-card">
        <div class="sidebar-head">💾 Запази промениете</div>
        <div class="pub-box">
          <div class="pub-info">
            <div class="pub-row"><span class="pub-label">Автор</span><span class="pub-val"><?= htmlspecialchars($uname) ?></span></div>
            <div class="pub-row"><span class="pub-label">Създадена</span><span class="pub-val"><?= date('d M Y', strtotime($article['created_at'])) ?></span></div>
            <div class="pub-row"><span class="pub-label">Категория</span><span class="pub-val"><?= htmlspecialchars($article['category']) ?></span></div>
          </div>
          <button type="submit" class="btn-update" onclick="return prepareSubmit()">Запази промениете ✓</button>
          <a href="article.php?id=<?= $article['id'] ?>" class="btn-secondary">👁️ Виж статията</a>
          <a href="/sportnews/admin/delete_article.php?id=<?= $article['id'] ?>&redirect=home"
             class="btn-danger-sm"
             onclick="return confirm('Delete this article? This cannot be undone.')">🗑️ Изтрий статията</a>
        </div>
      </div>

      <!-- IMAGE -->
      <div class="sidebar-card">
        <div class="sidebar-head">📸 Изображение на статията</div>
        <div class="upload-wrap">
          <?php if (!empty($article['image'])): ?>
            <img src="<?= htmlspecialchars($article['image']) ?>" class="current-img" id="imagePreview" alt="Current image">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#6b7280;margin-bottom:10px;">
              <input type="checkbox" name="remove_image" value="1" onchange="toggleRemoveImg(this)">
              Премахване на текущото изображение
            </label>
          <?php else: ?>
            <img id="imagePreview" class="current-img" src="" alt="" style="display:none;">
          <?php endif; ?>
          <div class="upload-area" id="uploadArea" onclick="document.getElementById('imageFile').click()">
            <div class="upload-icon">🖼️</div>
            <div class="upload-text">Кликнете, за да качите ново изображение</div>
            <div class="upload-sub">JPG, PNG, WEBP · макс 5MB</div>
          </div>
          <input type="file" id="imageFile" name="image" accept="image/*" style="display:none" onchange="previewImage(this)">
        </div>
      </div>

      <!-- CATEGORY -->
      <div class="sidebar-card">
        <div class="sidebar-head">🏷️ Категория</div>
        <div class="cat-grid">
          <?php
          $cats = ['Футбол'=>'⚽','Баскетбол'=>'🏀','Тенис'=>'🎾','Ф1'=>'🏎️','Ръгби'=>'🏉','Голф'=>'⛳','Бокс'=>'🥊','Олимпийски игри'=>'🏋️','Крикет'=>'🏏','Плуване'=>'🏊'];
          
          foreach ($cats as $c => $e):
          ?>
            <div class="cat-chip <?= $article['category'] === $c ? 'selected' : '' ?>" onclick="selectCat(this,'<?= $c ?>')"><?= $e ?> <?= $c ?></div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- LEAGUE -->
      <div class="sidebar-card">
        <div class="sidebar-head">🏆 Лига</div>
        <div class="league-wrap">
          <label>Competition</label>
          <select name="league" id="leagueSelect">
            <option value="">— None —</option>
            <?php
            $currentLeagues = $leaguesBySport[$article['category']] ?? [];
            foreach ($currentLeagues as $l):
            ?>
              <option value="<?= htmlspecialchars($l) ?>" <?= ($article['league'] ?? '') === $l ? 'selected' : '' ?>><?= htmlspecialchars($l) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- EMOJI -->
      <div class="sidebar-card">
        <div class="sidebar-head">🖼️ Миниатюрни емотикони</div>
        <div class="emoji-grid">
          <?php foreach (['⚽','🏀','🎾','🏎️','🏉','⛳','🥊','🏋️','🏏','🏊','🔥','⚡'] as $em): ?>
            <div class="emoji-opt <?= $article['emoji'] === $em ? 'selected' : '' ?>" onclick="selectEmoji(this,'<?= $em ?>')"><?= $em ?></div>
          <?php endforeach; ?>
        </div>
        <div class="emoji-custom-wrap">
          <label>Или напишете свой собствен</label>
          <input type="text" id="customEmoji" placeholder="e.g. 🏆" maxlength="4" value="<?= htmlspecialchars($article['emoji']) ?>" oninput="selectEmoji(null, this.value)">
        </div>
      </div>

    </div>
  </div>
</form>

<script>
const editor = document.getElementById('editor');

// Load existing content
editor.innerHTML = <?= json_encode($article['content']) ?>;
updateCount();

function fmt(cmd) { document.execCommand(cmd, false, null); editor.focus(); }
function fmtBlock(tag) { document.execCommand('formatBlock', false, tag); editor.focus(); }
function insertLink() {
    const url = prompt('Enter URL:');
    if (url) document.execCommand('createLink', false, url);
    editor.focus();
}

editor.addEventListener('input', updateCount);
function updateCount() {
    const text = editor.innerText.trim();
    const words = text ? text.split(/\s+/).filter(w => w).length : 0;
    document.getElementById('wordCount').textContent = words + ' words';
    document.getElementById('charCount').textContent = text.length + ' characters';
}

// Category
const leaguesBySport = {
    'Футбол':    ['Висша лига','Ла лига','Серия A','Бундеслига','Лига 1','Шампионска лига','Световно първеснтво'],
    'Баскетбол':  ['NBA','EuroLeague','NCAA'],
    'Тенис':      ['ATP Tour','WTA Tour','Grand Slam'],
    'Ф1':   ['F1 World Championship','F2','F3'],
    'Ръгби':       ['Six Nations','World Cup','Premiership'],
    'Голф':        ['PGA Tour','European Tour','Majors'],
    'Бокс':      ['Тежка катигория','Средна катигория','Лека категория'],
    'Олимпийски игри':    ['Летни Олимпийски игри','Зимни Олимпийски игри'],
    'Крикет':     ['Test Cricket','ODI','T20'],
    'Плуване':    ['World Aquatics','Olympics'],
};

function selectCat(el, val) {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('categoryInput').value = val;

    // Update league dropdown
    const sel = document.getElementById('leagueSelect');
    const leagues = leaguesBySport[val] || [];
    sel.innerHTML = '<option value="">— None —</option>';
    leagues.forEach(l => {
        const opt = document.createElement('option');
        opt.value = l; opt.textContent = l;
        sel.appendChild(opt);
    });
}

function selectEmoji(el, val) {
    document.querySelectorAll('.emoji-opt').forEach(e => e.classList.remove('selected'));
    if (el) el.classList.add('selected');
    document.getElementById('emojiInput').value = val;
}

// Image preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const p = document.getElementById('imagePreview');
            p.src = e.target.result;
            p.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleRemoveImg(cb) {
    const p = document.getElementById('imagePreview');
    if (p) p.style.opacity = cb.checked ? '0.3' : '1';
}

// Submit
function prepareSubmit() {
    document.getElementById('contentInput').value = editor.innerHTML;
    const cat   = document.getElementById('categoryInput').value;
    const emoji = document.getElementById('emojiInput').value;
    if (!cat)   { alert('Please select a category!'); return false; }
    if (!emoji) { alert('Please select an emoji!'); return false; }
    return true;
}

// Paste plain text
editor.addEventListener('paste', function(e) {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text');
    document.execCommand('insertText', false, text);
});
</script>
</body>
</html>