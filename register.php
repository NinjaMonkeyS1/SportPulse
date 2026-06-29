<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);
    $_SESSION['user'] = ['name' => $name, 'email' => $email];
    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    $error = "This email is already registered.";  // ← СМЕНИ ТАЗИ ЛИНИЯ
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — SportPulse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #fff;
}

/* LEFT PANEL */
.left-panel {
    background: #0f1117;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px;
    position: relative;
    overflow: hidden;
}

.left-panel::before {
    content: '';
    position: absolute;
    top: -120px; right: -120px;
    width: 450px; height: 450px;
    background: rgba(230,57,70,0.08);
    border-radius: 50%;
}

.left-panel::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -60px;
    width: 320px; height: 320px;
    background: rgba(230,57,70,0.05);
    border-radius: 50%;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    position: relative;
    z-index: 1;
}

.brand-dot {
    width: 10px; height: 10px;
    background: #e63946;
    border-radius: 50%;
    animation: pulse 2s infinite;
    box-shadow: 0 0 10px rgba(230,57,70,0.5);
}

@keyframes pulse {
    0%,100% { opacity:1; transform:scale(1); }
    50% { opacity:0.5; transform:scale(0.7); }
}

.brand-name {
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    letter-spacing: -0.5px;
}

.brand-name span { color: #e63946; }

.left-content {
    position: relative;
    z-index: 1;
}

.left-content h1 {
    font-size: 40px;
    font-weight: 900;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -1px;
    margin-bottom: 16px;
}

.left-content h1 span { color: #e63946; }

.left-content p {
    font-size: 15px;
    color: rgba(255,255,255,0.5);
    line-height: 1.7;
    max-width: 340px;
}

.stats {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.stat-box {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 16px;
}

.stat-box .num {
    font-size: 26px;
    font-weight: 900;
    color: #fff;
    letter-spacing: -1px;
    margin-bottom: 2px;
}

.stat-box .num span { color: #e63946; }

.stat-box .label {
    font-size: 12px;
    color: rgba(255,255,255,0.4);
    font-weight: 500;
}

/* RIGHT PANEL */
.right-panel {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px 64px;
    background: #ffdbdb;
    overflow-y: auto;
}

.right-panel h2 {
    font-size: 26px;
    font-weight: 800;
    color: #0f1117;
    letter-spacing: -0.5px;
    margin-bottom: 6px;
}

.right-panel .subtitle {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 28px;
}

.error-msg {
    background: #fff0f1;
    border: 1.5px solid #fca5a5;
    color: #e63946;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 18px;
}

.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.form-group { margin-bottom: 14px; }

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    background: #f8f9fc;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 16px;
    color: #0f1117;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
}

.form-group input:focus {
    border-color: #e63946;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(230,57,70,0.1);
}

.form-group input::placeholder { color: #c0c4cc; }

.terms {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 20px;
    margin-top: 4px;
}

.terms input { accent-color: #e63946; margin-top: 2px; flex-shrink: 0; }

.terms label {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
}

.terms a { color: #e63946; text-decoration: none; font-weight: 500; }

.btn-submit {
    width: 100%;
    background: #e63946;
    color: #fff;
    border: none;
    padding: 13px;
    border-radius: 10px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 18px;
}

.btn-submit:hover {
    background: #c1121f;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(230,57,70,0.3);
}

.login-link {
    text-align: center;
    font-size: 14px;
    color: #6b7280;
}

.login-link a {
    color: #e63946;
    font-weight: 600;
    text-decoration: none;
}

.login-link a:hover { text-decoration: underline; }

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #6b7280;
    text-decoration: none;
    margin-bottom: 28px;
    transition: color 0.15s;
}

.back-link:hover { color: #e63946; }

.right-panel { animation: fadeIn 0.4s ease; }
.left-panel  { animation: fadeIn 0.4s ease; }
@keyframes fadeIn {
    from { opacity:0; transform:translateX(10px); }
    to   { opacity:1; transform:translateX(0); }
}

@media (max-width: 768px) {
    body { grid-template-columns: 1fr; }
    .left-panel { display: none; }
    .right-panel { padding: 40px 28px; }
    .form-row-2 { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
    <a href="index.php" class="brand">
        <div class="brand-dot"></div>
        <span class="brand-name">Sport<span>Pulse</span></span>
    </a>

    <div class="left-content">
        <h1>Join the <span>Sports</span> Community.</h1>
        <p>Get access to live scores, personalised news feeds, and breaking alerts from every major sport.</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="num">50<span>+</span></div>
            <div class="label">Sports covered</div>
        </div>
        <div class="stat-box">
            <div class="num">24<span>/7</span></div>
            <div class="label">Live coverage</div>
        </div>
        <div class="stat-box">
            <div class="num">100<span>k+</span></div>
            <div class="label">Active readers</div>
        </div>
        <div class="stat-box">
            <div class="num">Free<span>.</span></div>
            <div class="label">Always free</div>
        </div>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
    <a href="index.php" class="back-link">← Back to SportPulse</a>

    <h2>Create your account 🎉</h2>
    <p class="subtitle">Free forever — no credit card required</p>

    <?php if ($error): ?>
        <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="John Doe" required autofocus>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="you@example.com" required>
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min 8 characters" minlength="8" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" placeholder="Repeat password" required>
            </div>
        </div>

        <div class="terms">
            <input type="checkbox" id="terms" required>
            <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
        </div>

        <button type="submit" class="btn-submit">Create Free Account →</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Sign in</a>
    </div>
</div>

</body>
</html>