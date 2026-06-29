<?php
// СЛУЖЕБНА КОНФИГУРАЦИЯ
$site_name   = "SportPulse";
$site_url    = "/"; // Линк към началната страница, ако няма история в браузъра
$company_name = "Спорт Пулс Медия ЕООД";
$eik          = "202612345";
$email        = "privacy@sportpulse.bg";
$last_update  = "26 юни 2026 г.";
?>
<!DOCTYPE html>
<html lang="bg" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика за поверителност - <?php echo $site_name; ?></title>
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

        /* Навигационна лента за бутоните */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        /* Стилове за бутоните */
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

        ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        li {
            margin-bottom: 10px;
            font-size: 15px;
        }

        li strong {
            color: var(--title-color);
        }

        .contact-info {
            background-color: rgba(255, 71, 87, 0.05);
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
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
    
    <!-- Горна лента с бутони -->
    <div class="top-nav">
        <!-- Интелигентен бутон Назад -->
        <a href="<?php echo $site_url; ?>" class="btn btn-back" id="backBtn">
            &#8592; Назад
        </a>
    </div>

    <header>
        <div class="logo"><?php echo substr($site_name, 0, -5); ?><span><?php echo substr($site_name, -5); ?></span></div>
        <h1>Политика за поверителност</h1>
        <div class="date">Последна промяна: <?php echo $last_update; ?></div>
    </header>

    <main>
        <section>
            <p>В <strong><?php echo $site_name; ?></strong> пазим твоите данни и уважаваме поверителността ти. Тази страница накратко обяснява какво събираме и защо ни е необходимо, когато ползваш нашия уебсайт.</p>
        </section>

        <section>
            <h2>1. Какви данни събираме и защо?</h2>
            <ul>
                <li><strong>За коментари и контакт:</strong> Ако оставиш коментар или ни пишеш през формата, събираме твоето име/псевдоним и имейл адрес, за да знаем кой ни търси.</li>
                <li><strong>За нюзлетър:</strong> Ако се запишеш за спортни известия, пазим твоя имейл. Можеш да се откажеш с 1 клик по всяко време.</li>
                <li><strong>Автоматична статистика:</strong> Ползваме системи като Google Analytics, за да разберем кои спортни новини са най-четени. Тези данни са напълно анонимни.</li>
            </ul>
        </section>

        <section>
            <h2>2. Споделяне на данните</h2>
            <p><strong>Категорично не.</strong> Не продаваме личните ти данни на трети лица. Използваме ги единствено ние и софтуерните модули, нужни за поддръжката на сайта и показването на реклами.</p>
        </section>

        <section>
            <h2>3. Твоите права</h2>
            <p>Ти контролираш всичко. По всяко време можеш да ни изпратиш имейл и да изискаш:</p>
            <ul>
                <li>Справка какви данни имаме за теб.</li>
                <li>Корекция на информацията.</li>
                <li>Пълно изтриване на профила и хронологията ти.</li>
            </ul>
        </section>

        <section>
            <h2>4. Администратор на сайта</h2>
            <p>За въпроси относно поверителността, се свържи с нас тук:</p>
            <div class="contact-info">
                <p><strong>Фирма / Администратор:</strong> <?php echo $company_name; ?></p>
                <p><strong>ЕИК:</strong> <?php echo $eik; ?></p>
                <p><strong>Официален имейл:</strong> <a href="mailto:<?php echo $email; ?>" style="color: #ff4757; text-decoration: none; font-weight: bold;"><?php echo $email; ?></a></p>
            </div>
        </section>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> <?php echo $site_name; ?>. Всички права запазени.
    </footer>
</div>

<script>
    // JS 1: Логика за бутона "Назад"
    const backBtn = document.getElementById('backBtn');
    // Проверяваме дали потребителят има история в текущия таб и дали е дошъл от същия домейн
    if (document.referrer && window.history.length > 1) {
        backBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Спира пренасочването към началната страница
            window.history.back(); // Връща го стъпка назад
        });
    }

</script>

</body>
</html>