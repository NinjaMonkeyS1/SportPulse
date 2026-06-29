<?php
// СЛУЖЕБНА КОНФИГУРАЦИЯ
$site_name   = "SportPulse";
$site_url    = "/"; // Линк към началната страница
$founded_year = "2026";
$email        = "office@sportpulse.bg"; // Основен имейл на редакцията
?>
<!DOCTYPE html>
<html lang="bg" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>За нас - <?php echo $site_name; ?></title>
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
            --tag-bg: #f1f2f6;
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
            --tag-bg: #2c323f;
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
            font-size: 24px;
            color: var(--title-color);
            margin-top: 10px;
        }

        section {
            margin-bottom: 35px;
        }

        h2 {
            font-size: 20px;
            color: var(--title-color);
            margin-bottom: 15px;
            border-left: 4px solid #ff4757;
            padding-left: 12px;
        }

        p {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .sports-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .tag {
            background-color: var(--tag-bg);
            color: var(--title-color);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background-color: rgba(255, 71, 87, 0.05);
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 28px;
            color: #ff4757;
            margin-bottom: 5px;
        }

        .stat-card p {
            margin-bottom: 0;
            font-size: 14px;
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

        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="top-nav">
        <a href="<?php echo $site_url; ?>" class="btn btn-back" id="backBtn">
            &#8592; Към сайта
        </a>
    </div>

    <header>
        <div class="logo"><?php echo substr($site_name, 0, -5); ?><span><?php echo substr($site_name, -5); ?></span></div>
        <h1>Кои сме ние?</h1>
    </header>

    <main>
        <section>
            <h2>Нашата мисия</h2>
            <p><strong><?php echo $site_name; ?></strong> е модерна независима медия, създадена от фенове за фенове. Нашата цел е проста: да улавяме пулса на спорта и да ти го доставяме в реално време – бързо, точно и без филтър.</p>
            <p>Не обичаме скучната статистика и сухите текстове. Тук ще намериш горещи новини, ексклузивни анализи, коментари на живо и всичко важно от света на големия спорт.</p>
        </section>

        <section>
            <h2>Какво покриваме?</h2>
            <p>Следим всичко най-интересно на терена, пистата и в залата. Нашите основни фокуси включват:</p>
            <div class="sports-tags">
                <span class="tag">⚽ Футбол</span>
                <span class="tag">🏀 Баскетбол</span>
                <span class="tag">🎾 Тенис</span>
                <span class="tag">🏎️ Формула 1</span>
                <span class="tag">🥊 Бойни спортове</span>
                <span class="tag">🎮 Е-спортове</span>
            </div>
        </section>

        <section>
            <h2>Защо да четеш SportPulse?</h2>
            <p>Защото знаем какво е да тръпнеш пред екрана до последната секунда. Ние гарантираме:</p>
            <ul>
                <li style="margin-bottom: 8px; margin-left: 20px;"><strong>Бързина:</strong> Новините стигат до теб секунди след като са се случили.</li>
                <li style="margin-bottom: 8px; margin-left: 20px;"><strong>Обективност:</strong> Без пристрастия и без фалшиви сензации.</li>
                <li style="margin-bottom: 8px; margin-left: 20px;"><strong>Общност:</strong> Място, където твоето мнение в коментарите има значение.</li>
            </ul>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>24/7</h3>
                    <p>Спортни новини в реално време</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $founded_year; ?></h3>
                    <p>Година на основаване</p>
                </div>
            </div>
        </section>

        <section>
            <h2>Свържи се с редакцията</h2>
            <p>Имаш гореща новина, сигнал, предложение за партньорство или просто искаш да ни кажеш колко сме яки? Пиши ни без колебание на:</p>
            <p><a href="mailto:<?php echo $email; ?>" style="color: #ff4757; text-decoration: none; font-weight: bold; font-size: 18px;"><?php echo $email; ?></a></p>
        </section>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> <?php echo $site_name; ?>. Всички права запазени.
    </footer>
</div>

<script>
    // JS 1: Логика за бутона "Назад"
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