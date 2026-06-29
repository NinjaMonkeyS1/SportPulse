<?php
// СЛУЖЕБНА КОНФИГУРАЦИЯ
$site_name   = "SportPulse";
$site_url    = "index.php"; 
$my_email    = "office@sportpulse.bg";
$phone       = "+359 888 777 999";
$address     = "гр. Сливен";

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = strip_tags(trim($_POST["name"]));
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = strip_tags(trim($_POST["message"]));

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_msg = "Моля, попълнете всички полета.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Въведеният имейл адрес не е валиден.";
    } else {
        $to = $my_email;
        $email_subject = "Контактна форма: $subject";
        $email_content = "От: $name\nИмейл: $email\n\nСъобщение:\n$message\n";
        $headers = "From: $email\r\nReply-To: $email";

        if (mail($to, $email_subject, $email_content, $headers)) {
            $success_msg = "Вашето съобщение беше изпратено успешно.";
            $name = $email = $subject = $message = "";
        } else {
            $error_msg = "Възникна грешка. Моля, опитайте отново по-късно.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bg" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакти — <?php echo $site_name; ?></title>
    <style>
        /* ТОЧНИТЕ СТИЛОВЕ ОТ ТВОЯ КЛАСИЧЕСКИ ВАРИАНТ */
        :root[data-theme="light"] {
            --bg-color: #f1f2f6;
            --card-bg: #ffffff;
            --text-color: #57606f;
            --title-color: #2f3542;
            --border-color: #e4e7eb;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --btn-secondary: #e4e7eb;
            --btn-secondary-text: #2f3542;
            --input-bg: #ffffff;
        }
        :root[data-theme="dark"] {
            --bg-color: #11141a;
            --card-bg: #1b1f27;
            --text-color: #9da5b4;
            --title-color: #ffffff;
            --border-color: #2c323f;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            --btn-secondary: #2c323f;
            --btn-secondary-text: #ffffff;
            --input-bg: #11141a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: var(--box-shadow);
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .btn {
            border: none;
            padding: 10px 18px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            gap: 8px;
        }

        .btn-back {
            background-color: var(--btn-secondary);
            color: var(--btn-secondary-text);
        }

        .btn-theme {
            background-color: #ff4757;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        header {
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .logo {
            font-size: 32px;
            font-weight: 900;
            color: var(--title-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .logo span {
            color: #ff4757;
        }

        h1 {
            font-size: 22px;
            color: var(--title-color);
            margin-top: 10px;
        }

        /* ПОДРЕДБА НА СЪДЪРЖАНИЕТО В СТИЛА НА СТРАНИЦАТА */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
        }

        h2 {
            font-size: 18px;
            color: var(--title-color);
            margin-bottom: 15px;
            border-left: 4px solid #ff4757;
            padding-left: 12px;
        }

        p {
            margin-bottom: 15px;
            font-size: 15px;
        }

        .info-group {
            margin-bottom: 20px;
        }

        .info-group strong {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8892b0;
            margin-bottom: 4px;
        }

        .info-group p, .info-group a {
            margin: 0;
            font-size: 16px;
            color: var(--title-color);
            text-decoration: none;
        }

        .info-group a:hover {
            color: #ff4757;
        }

        /* СТИЛОВЕ НА ФОРМАТА, СЪОБРАЗЕНИ С ТЕМАТА */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--title-color);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            color: var(--title-color);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff4757;
        }

        .btn-submit {
            background-color: #ff4757;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
            transition: opacity 0.2s;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        /* АЛЕРТИ СЪС СЪЩИТЕ ЗAOБЛЕНИ ЪГЛИ */
        .alert {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        .alert-success { background-color: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.3); }
        .alert-error { background-color: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.3); }

        footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 13px;
            color: #8892b0;
        }

        @media (max-width: 680px) {
            .contact-grid { grid-template-columns: 1fr; gap: 30px; }
            .container { padding: 25px; }
        }
    </style>
    <script>
    // Проверява и прилага темата мигновено, за да няма премигване
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
</script>
</head>
<body>

<div class="container">
    
    <div class="top-nav">
        <a href="<?php echo $site_url; ?>" class="btn btn-back">← Назад</a>
    </div>

    <header>
        <div class="logo"><?php echo substr($site_name, 0, 5); ?><span><?php echo substr($site_name, 5); ?></span></div>
        <h1>Контактна форма</h1>
    </header>

    <main>
        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="contact-grid">
            
            <!-- ЛЯВО: ДЕТАЙЛИ -->
            <div>
                <h2>Информация</h2>
                
                <div class="info-group">
                    <strong>Електронна поща</strong>
                    <a href="mailto:<?php echo $my_email; ?>"><?php echo $my_email; ?></a>
                </div>

                <div class="info-group">
                    <strong>Телефон</strong>
                    <p><?php echo $phone; ?></p>
                </div>

                <div class="info-group">
                    <strong>Адрес</strong>
                    <p><?php echo $address; ?></p>
                </div>
            </div>

            <!-- ДЯСНО: ФОРМА -->
            <div>
                <h2>Пишете ни</h2>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="name">Вашето име</label>
                        <input type="text" name="name" id="name" class="form-control" required value="<?php echo isset($name) ? $name : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Имейл адрес</label>
                        <input type="email" name="email" id="email" class="form-control" required value="<?php echo isset($email) ? $email : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="subject">Тема</label>
                        <input type="text" name="subject" id="subject" class="form-control" required value="<?php echo isset($subject) ? $subject : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="message">Съобщение</label>
                        <textarea name="message" id="message" rows="5" class="form-control" required><?php echo isset($message) ? $message : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Изпрати</button>
                </form>
            </div>

        </div>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> <?php echo $site_name; ?>. Всички права запазени.
    </footer>
    <script>
    // Прилагане на тъмната тема веднага при зареждане
    document.documentElement.setAttribute('data-theme', 'dark');
</script>
</div>
</body>
</html>