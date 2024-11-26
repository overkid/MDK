<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_cookie'])) {
        setcookie('total_time_spent', $_COOKIE['total_time_spent'] ?? 0, time() + 3600 * 24 * 30);
        setcookie('last_visit_time', time(), time() + 3600 * 24 * 30);
        echo "<p class='echo'>• Куки установлены!</p>";
    } elseif (isset($_POST['show_cookie'])) {
        echo "<p class='echo'>• Общее время на сайте: " . gmdate("H:i:s", $_COOKIE['total_time_spent'] ?? 0) . "</p>";
    } elseif (isset($_POST['delete_cookie'])) {
        setcookie('total_time_spent', '', time() - 3600);
        setcookie('last_visit_time', '', time() - 3600);
        $totalTimeSpent = 0;
        echo "<p class='echo'>• Куки удалены!</p>";
    }
}

if (!isset($_POST['delete_cookie'])) {
    if (isset($_COOKIE['last_visit_time']) && isset($_COOKIE['total_time_spent'])) {
        $lastVisitTime = $_COOKIE['last_visit_time'];
        $timeSpent = time() - $lastVisitTime;
        $totalTimeSpent = ($_COOKIE['total_time_spent'] ?? 0) + $timeSpent;

        setcookie('last_visit_time', time(), time() + 3600 * 24 * 30);
        setcookie('total_time_spent', $totalTimeSpent, time() + 3600 * 24 * 30);
    } else {
        $totalTimeSpent = $_COOKIE['total_time_spent'] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Таймер</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Pixelify+Sans:wght@400..700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Manrope:wght@200..800&family=Pixelify+Sans:wght@400..700&display=swap');
    </style>
</head>
<body>

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    :root {
        --font-family: "Manrope", sans-serif;
        --second-family: "Pixelify Sans", sans-serif;
    }
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    .content {
        margin-top: 80px;
        width: 400px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    h1 {
        font-family: var(--font-family);
        font-weight: 700;
        font-size: 64px;
        text-align: center;
        color: #000;
        margin-bottom: 16px;
    }
    .echo {
        margin-top: 24px;
        font-family: var(--font-family);
        font-weight: 500;
        font-size: 20px;
        text-align: center;
        color: #606060;
    }
    .desc {
        font-family: var(--font-family);
        font-weight: 500;
        font-size: 20px;
        text-align: center;
        color: #606060;
        margin-bottom: 32px;
    }
    .timer {
        font-family: var(--second-family);
        font-weight: 400;
        font-size: 64px;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #000;
        width: 322px;
        height: 97px;
        margin-top: 40px;
        margin-bottom: 16px;
    }
    hr {
        width: 322px;
        height: 2px;
        border-top: 2px solid #d9d9d9;
    }
    .timer-desc {
        font-family: var(--font-family);
        font-weight: 500;
        font-size: 16px;
        line-height: 22px;
        text-align: center;
        color: #606060;
        margin-bottom: 32px;
    }
    button {
        background-color: white;
        border: none;
        font-family: var(--font-family);
        font-weight: 500;
        font-size: 16px;
        text-decoration: underline;
        text-align: center;
        color: #606060;
        cursor: pointer;
    }
    form {
        display: flex;
        justify-content: center;
        width: 322px;
        flex-wrap: wrap;
        gap: 16px 32px;
        margin-top: 32px;
    }
    </style>

    <div class="content">
        <h1>Это таймер</h1>
        <p class="desc">Общее время, проведенное<br>на этом сайте:</p>
        <hr>
        <p class="timer"><?php echo gmdate("H:i:s", $totalTimeSpent); ?></p>
        <p class="timer-desc">(отправь Cookie и обновляй страницу)</p>
        <hr>
        <form method="post">
            <button type="submit" name="set_cookie">Отправить Cookie</button>
            <button type="submit" name="show_cookie">Показать Cookie</button>
            <button type="submit" name="delete_cookie">Удалить Cookie</button>
        </form>
    </div>
</body>
</html>