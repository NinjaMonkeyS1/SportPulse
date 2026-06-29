<?php
// СЛУЖЕБНА КОНФИГУРАЦИЯ
$site_name   = "SportPulse";
$site_url    = "/"; 
$email        = "privacy@sportpulse.bg"; 
$last_update  = "26 юни 2026 г.";
?>
<!DOCTYPE html>
<html lang="bg" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика за бисквитки - <?php echo $site_name; ?></title>
    <style>
        :root[data-theme="light"] {
            --bg-color: #f1f2f6;
            --card-bg: #ffffff;
            --text-color: #57606f;
            --title-color: #2f3542;
            --border-color: #e4e7eb;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --btn-secondary: #e4e7eb;
            --btn-secondary-text: #2f3542;
            --table-header: #f8f9fa;
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
            --table-header: #2c323f;
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

        .date {
            font-size: 13px;
            color: #8892b0;
            font-style: italic;
            margin-top: 5px;
        }

        section {
            margin-bottom: 35px;
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

        /* Стилове за таблицата с бисквитки */
        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }

        .cookie-table th, .cookie-table td {
            border: 1px solid var(--border-color);
            padding: 12px;
            text-align: left;
        }

        .cookie-table th {
            background-color: var(--table-header);
            color: var(--title-color);
            font-weight: 600;
        }

        footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 13px;
            color: #8892b0;
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="top-nav">
        <a href="<?php echo $site_url; ?>" class="btn btn-back" id="backBtn">
            &#8592; Назад
        </a>
    </div>

    <header>
        <div class="logo"><?php echo substr($site_name, 0, -5); ?><span><?php echo substr($site_name, -5); ?></span></div>
        <h1>Политика за бисквитки (Cookies)</h1>
        <div class="date">Последна промяна: <?php echo $last_update; ?></div>
    </header>

    <main>
        <section>
            <p>За да работи <strong><?php echo $site_name; ?></strong> правилно, бързо и да ти показва най-интригуващото спортно съдържание, ние използваме малки текстови файлове, наречени „бисквитки“ (cookies). Тук ще разбереш какви са те и как ги контролираш.</p>
        </section>

        <section>
            <h2>1. Какво са бисквитките?</h2>
            <p>Бисквитките са миниатюрни текстови файлове, които се запазват на твоя компютър или телефон, когато посещаваш даден уебсайт. Те помагат на сайта да помни твоите настройки (като език, размери на шрифта или избор на тъмна тема) за определен период от време.</p>
        </section>

        <section>
            <h2>2. Какви бисквитки използваме?</h2>
            <p>Нашият сайт използва следните основни категории бисквитки:</p>
            
            <table class="cookie-table">
                <thead>
                    <tr>
                        <th>Тип</th>
                        <th>Цел</th>
                        <th>Продължителност</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Задължителни</strong></td>
                        <td>Нужни за базовата работа на сайта. Например: за да запомнят дали си избрал Тъмна или Светла тема.</td>
                        <td>Сесийни / До 1 година</td>
                    </tr>
                    <tr>
                        <td><strong>Аналитични (Google)</strong></td>
                        <td>Помагат ни да разберем колко хора ни четат и кои спортни категории са най-популярни.</td>
                        <td>До 2 години (Анонимни)</td>
                    </tr>
                    <tr>
                        <td><strong>Рекламни (AdSense)</strong></td>
                        <td>Използват се за показване на реклами, които съответстват на твоите интереси, вместо случайни банери.</td>
                        <td>Различно (Доставчик: Google)</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section>
            <h2>3. Как да ги контролираш?</h2>
            <p>Ти решаваш! Можеш да изтриеш всички бисквитки, които вече са на твоя компютър, а повечето съвременни браузъри ти позволяват изобщо да ги блокираш.</p>
            <p>Имай предвид, че ако изключиш задължителните бисквитки, някои функции в <?php echo $site_name; ?> (като запазването на избраната от теб тема) няма да работят коректно след презареждане на страницата.</p>
        </section>

        <section>
            <h2>4. Въпроси</h2>
            <p>Ако имаш въпроси относно нашите бисквитки, драсни ни един ред на: <a href="mailto:<?php echo $email; ?>" style="color: #ff4757; text-decoration: none; font-weight: bold;"><?php echo $email; ?></a></p>
        </section>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> <?php echo $site_name; ?>. Всички права запазени.
    </footer>
</div>

<script>
    // JS 1: Бутон Назад
    const backBtn = document.getElementById('backBtn');
    if (document.referrer && window.history.length > 1) {
        backBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.history.back();
        });
    }

</script>

</body>
</html>